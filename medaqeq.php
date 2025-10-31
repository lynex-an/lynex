<?php
error_reporting(E_ALL); 
ini_set('display_errors', 1);
session_start(); // هذا هو السطر الأصلي في الملف
// ... بقية الكود
session_start(); // 🛑 يجب تفعيل الجلسات لاستخدام الـ $_SESSION لحفظ مسارات الملفات

// إعدادات البداية
$output_html = "";
$analysis_script_name = "";
$project_name = "محلل LYNEX"; 
$uploadOk = 1;
$uploaded_file_path = ""; 
$cleaned_file_path = ""; 
$analysis_status_message = ""; 

// مجلدات الرفع
$target_dir = "uploads/";

// دالة لتنظيف اسم الملف لإنشاء اسم الملف المعالج
function get_cleaned_filename($original_path) {
    $path_info = pathinfo($original_path);
    // إضافة لاحقة (مثلاً: _cleaned) قبل الامتداد
    return $path_info['dirname'] . '/' . $path_info['filename'] . '_cleaned.' . $path_info['extension'];
}


// التحقق من إرسال النموذج
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. تحديد المعاملات والتحقق من الرفع
    $original_file_name = basename($_FILES["data_file"]["name"]); 
    $unique_prefix = uniqid("analysis_");
    // 💡 نقطة أمان إضافية: تنظيف اسم الملف الأصلي من أي حروف ضارة
    $safe_file_name = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $original_file_name);
    $uploaded_file_path = $target_dir . $unique_prefix . $safe_file_name;
    
    // تحديد نوع التحليل من الزر المضغوط
    if (isset($_POST['analyze_duplicates'])) {
        $analysis_script_name = "duplicate_data.py";
    } elseif (isset($_POST['analyze_missing'])) {
        $analysis_script_name = "missing_data.py";
    } elseif (isset($_POST['analyze_inconsistent'])) {
        $analysis_script_name = "inconsistent_data.py";
    } elseif (isset($_POST['analyze_irrelevant'])) {
        $analysis_script_name = "irrelevant_data.py";
    } else {
        $analysis_status_message = "❌ خطأ: لم يتم تحديد نوع التحليل.";
        $uploadOk = 0;
    }
    
    // 🛑 التقاط المعاملات الجديدة من حقول الإدخال
    $columns = isset($_POST['columns']) ? trim($_POST['columns']) : "all";
    $threshold = isset($_POST['threshold']) ? trim($_POST['threshold']) : "85"; 
    $mode = "clean_only"; 

    // 1.1 معالجة الملف المرفوع
    if ($uploadOk == 1) {
        
        // 💡 تحقق إضافي لنوع الملف (MIME Type) 
        $allowed_mime = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        $file_mime = '';
        if (isset($_FILES["data_file"]["tmp_name"]) && $_FILES["data_file"]["error"] == UPLOAD_ERR_OK) {
             $file_mime = mime_content_type($_FILES["data_file"]["tmp_name"]);
        }
        
        if (!in_array($file_mime, $allowed_mime)) {
             $analysis_status_message = "❌ خطأ: نوع الملف غير مدعوم. يرجى رفع ملف CSV أو Excel.";
             $uploadOk = 0;
        }

        if ($uploadOk == 1 && move_uploaded_file($_FILES["data_file"]["tmp_name"], $uploaded_file_path)) {
            // تم الرفع بنجاح
            $cleaned_file_path = get_cleaned_filename($uploaded_file_path);
        } elseif ($uploadOk == 1) {
            $analysis_status_message = "❌ خطأ: حدث خطأ أثناء رفع الملف. (تحقق من إعدادات الخادم وصلاحيات مجلد 'uploads')";
            $uploadOk = 0;
        }
    }

    // 2. تشغيل أداة البايثون (إذا تم الرفع بنجاح)
    if ($uploadOk == 1) {
        
        $absolute_script_path = __DIR__ . "/analysis_tools/{$analysis_script_name}";
        
        // 🛑 التعديل الأمني: استخدام escapeshellarg() لكل مُعامل 🛑
        $safe_script_path = escapeshellarg($absolute_script_path);
        $safe_uploaded_path = escapeshellarg($uploaded_file_path);
        $safe_columns = escapeshellarg($columns);
        $safe_threshold = escapeshellarg($threshold);
        $safe_cleaned_path = escapeshellarg($cleaned_file_path);
        $safe_mode = escapeshellarg($mode);
        
        // بناء أمر التشغيل الآمن (يرسل 6 مُعاملات إلى Python)
        $python_command = "python {$safe_script_path} {$safe_uploaded_path} {$safe_columns} {$safe_threshold} {$safe_cleaned_path} {$safe_mode} 2>&1";
        
        // تشغيل الأمر 
        $output = shell_exec($python_command);
        
        // 3. تحليل النتائج (المخرجات القادمة من Python)
        if ($output === null) {
            $analysis_status_message = "❌ خطأ في النظام: لا يمكن تشغيل سكريبت البايثون. (تحقق من مسار Python)";
        } else {
            
            // 🛑 تحليل المخرجات المبسطة من Python 🛑
            $output_trimmed = trim($output);
            
            if (strpos($output_trimmed, 'No issues found') !== false) {
                 // حالة النجاح التام (ملف نظيف ولا يحتاج لتعديل)
                 $analysis_status_message = "✅ تم التدقيق بنجاح. لا توجد أي مشكلات تحتاج لمعالجة في بياناتك!";
            } elseif (strpos($output_trimmed, 'Cleaned file saved') !== false) {
                 // حالة التعديل والنجاح (تم العثور على مشاكل وتم معالجتها وحفظ الملف)
                 $analysis_status_message = "🎉 تم التدقيق وإصلاح المشاكل بنجاح. بياناتك جاهزة!";
            } else {
                // حالة الخطأ أو فشل Python (عرض الخطأ المباشر)
                $analysis_status_message = "⚠️ حدث خطأ أثناء التحليل. يرجى مراجعة الخطأ التالي لتحديد المشكلة:<br><pre>" . htmlspecialchars($output) . "</pre>";
                $cleaned_file_path = ""; // إزالة زر التحميل إذا فشل
            }
        }
        
        // 4. ربط المسارات بملف تحميل (download.php)
        $_SESSION['original_file'] = $uploaded_file_path;
        // نربط مسار الملف المعالج فقط إذا كانت العملية ناجحة
        if ($uploadOk == 1 && file_exists($cleaned_file_path)) {
             $_SESSION['cleaned_file'] = $cleaned_file_path;
        } else {
             unset($_SESSION['cleaned_file']); // التأكد من عدم وجود مسار خاطئ
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>المحلل - Lynex</title>
  <link rel="stylesheet" href="style.css"> 
  
  <style>
    /* التنسيقات الإضافية التي تم إدراجها مسبقاً */
    body { background: radial-gradient(circle at top left, #0b3c5d, #062e46); color: white; font-family: "Cairo", sans-serif; margin: 0; }
    header { background: rgba(0,0,0,0.2); padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; }
    header ul { list-style: none; display: flex; gap: 25px; margin: 0; padding: 0; }
    header a { text-decoration: none; color: #dbe9f4; font-weight: bold; }
    .logo img { height: 50px; }
    .analysis-container { padding: 50px; max-width: 800px; margin: 50px auto; background-color: rgba(255, 255, 255, 0.1); border-radius: 8px; }
    .analysis-form h2 { color: #dbe9f4; text-align: center; margin-bottom: 30px; }
    .form-group { margin-bottom: 20px; display: block; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #dbe9f4; }
    
    .form-group input[type="file"],
    .form-group input[type="text"] { /* 🛑 إضافة تنسيق لمدخل النص */
        width: 100%; background-color: #275677; color: white; border: 1px solid #1b4965; padding: 15px; border-radius: 5px; box-sizing: border-box;
    }
    
    .analysis-buttons, .download-buttons {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;
    }

    .analysis-buttons button, .download-buttons a {
        padding: 15px; background-color: #3282b8; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1.0em;
        font-weight: bold; transition: background-color 0.3s; text-align: center; text-decoration: none; display: block;
    }
    
    .analysis-buttons button:hover, .download-buttons a:hover { background-color: #5fa8d3; }
    
    .download-buttons { margin-top: 30px; padding: 20px; background-color: rgba(0, 0, 0, 0.2); border-radius: 8px; }
    .download-buttons a.original { background-color: #f7a000; } 
    .download-buttons a.cleaned { background-color: #4CAF50; } 
    
    .status-box {
        padding: 20px; border-radius: 8px; margin-top: 20px; font-weight: bold; text-align: right; font-size: 1.1em;
    }
    .success-status { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .error-status { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .neutral-status { background-color: #bee5eb; color: #0c5460; border: 1px solid #bbe5f2; }
  </style>
</head>
<body>

  <header>
    <div class="logo"><img src="logo.png" alt="Lynex Logo"></div>
    <div class="burger">
        <div></div><div></div><div></div>
    </div>
    <nav>
        <ul id="navLinks">
            <li><a href="index.php">الرئيسية</a></li>  
            <li><a href="medaqeq.php" class="active">المحلل</a></li> 
            <li><a href="contact.php">تواصل</a></li> 
            <li><a href="login.php">تسجيل دخول</a></li> 
        </ul>
    </nav>
  </header>

  <section class="analysis-container" style="display: block !important;">
    <div class="analysis-form">
      <h2>بدء تحليل جودة البيانات</h2>
      
      <form method="POST" action="medaqeq.php" enctype="multipart/form-data" style="display: block !important;">
        
        <div class="form-group">
            <label for="data_file">1. تحميل ملف البيانات (CSV/Excel):</label>
            <input type="file" name="data_file" id="data_file" required accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
        </div>
        
        <div class="form-group">
            <label for="columns">2. الأعمدة المراد تطبيق التحليل عليها (افصل بينها بفاصلة، أو اتركها "all" لجميع الأعمدة):</label>
            <input type="text" name="columns" id="columns" value="all" placeholder="مثال: Name, Email, Price">
        </div>

        <div class="form-group">
            <label for="threshold">3. عامل الحساسية (Threshold/Factor) (افتراضي: 85 - للتكرارات/الناقصة، 1.5 - للشاذة):</label>
            <input type="text" name="threshold" id="threshold" value="85" placeholder="القيمة الافتراضية 85، أو 1.5 للقيم الشاذة">
        </div>

        
        <label>4. اختر نوع التحليل:</label>
        <div class="analysis-buttons">
            <button type="submit" name="analyze_duplicates">🔍 بيانات مكررة</button>
            <button type="submit" name="analyze_missing">❌ بيانات ناقصة</button>
            <button type="submit" name="analyze_inconsistent">❓ بيانات غير متناسقة</button>
            <button type="submit" name="analyze_irrelevant">🗑️ بيانات شاذة/غير مهمة</button>
        </div>
      </form>
      
      <?php if (!empty($analysis_status_message)): ?>
          <?php 
            $status_class = 'neutral-status';
            if (strpos($analysis_status_message, '✅') !== false || strpos($analysis_status_message, '🎉') !== false) {
                $status_class = 'success-status';
            } elseif (strpos($analysis_status_message, '❌') !== false || strpos($analysis_status_message, '⚠️') !== false) {
                $status_class = 'error-status';
            }
          ?>
          <div class="status-box <?php echo $status_class; ?>">
              <?php echo $analysis_status_message; ?>
          </div>
          
          <div class="download-buttons">
              <?php if (isset($_SESSION['original_file'])): ?>
                  <a href="download.php?file=original" class="original">⬇️ تحميل الملف الأصلي</a>
              <?php endif; ?>
              
              <?php if (isset($_SESSION['cleaned_file'])): ?>
                  <a href="download.php?file=cleaned" class="cleaned">✨ تحميل الملف المُعدّل</a>
              <?php endif; ?>
          </div>
      <?php endif; ?>
      
    </div>
  </section>

  <footer>
    <p>© 2025 Lynex - جميع الحقوق محفوظة</p>
    <div class="social">
      تابعنا على <a href="https://x.com/LynexAi" target="_blank" style="color:#5fa8d3;">@LynexAi</a>
    </div>
  </footer>

</body>
</html>