<?php
// 🛑 ملف download.php لتنظيم تحميل الملفات المؤقتة 🛑
session_start();

$base_dir = __DIR__ . "/uploads/";
$file_to_download = null;

if (isset($_GET['file'])) {
    
    // 1. تحديد الملف المطلوب بناءً على معلمة URL
    if ($_GET['file'] === 'original' && isset($_SESSION['original_file'])) {
        $file_path = $_SESSION['original_file'];
        $clean_up = false;
    } elseif ($_GET['file'] === 'cleaned' && isset($_SESSION['cleaned_file'])) {
        $file_path = $_SESSION['cleaned_file'];
        $clean_up = true;
    } else {
        die("❌ خطأ: لم يتم تحديد الملف بشكل صحيح أو انتهت صلاحية الجلسة.");
    }
    
    // 2. التحقق من أمان المسار ووجود الملف
    // strpos($file_path, $base_dir) === 0: للتأكد من أن المسار يبدأ بمسار مجلد uploads الآمن
    if (isset($file_path) && strpos($file_path, $base_dir) === 0 && file_exists($file_path)) {
        
        $file_to_download = $file_path;
        $file_name = basename($file_path);
        
        // 3. إعداد الرؤوس (Headers) للتحميل
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_to_download));
        
        // 4. إخراج محتوى الملف
        readfile($file_to_download);
        
        // 5. التنظيف (حذف الملفات المؤقتة)
        if ($clean_up && file_exists($file_to_download)) {
             @unlink($file_to_download); // @: لإخفاء أي خطأ إذا لم يتمكن من الحذف
             unset($_SESSION['cleaned_file']);
        }
        
        exit;
    }
}

// إذا فشل التحميل أو المسار غير آمن
die("❌ خطأ: فشل التحميل. يرجى التأكد من أن الملف لم يتم حذفه.");