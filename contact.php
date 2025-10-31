<?php
ob_start();
include 'db_connect.php'; 

$contact_success = "";
$contact_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contact_submit'])) {
    
    $sender_name = $conn->real_escape_string(trim($_POST['contact_name']));
    $sender_email = $conn->real_escape_string(trim($_POST['contact_email']));
    $message_content = $conn->real_escape_string(trim($_POST['contact_message']));

    if (empty($sender_name) || empty($sender_email) || empty($message_content)) {
        $contact_error = "الرجاء تعبئة جميع الحقول المطلوبة.";
    } else {
        $sql = "INSERT INTO contact_messages (sender_name, sender_email, message_content, sent_at) 
                VALUES (?, ?, ?, NOW())";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sss", $sender_name, $sender_email, $message_content);
            
            if ($stmt->execute()) {
                header("Location: contact.php?status=success");
                exit();
            } else {
                $contact_error = "❌ حدث خطأ أثناء محاولة حفظ الرسالة في قاعدة البيانات: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $contact_error = "❌ حدث خطأ في إعداد الاستعلام: " . $conn->error;
        }
    }
}

if (isset($_GET['status']) && $_GET['status'] == 'success') {
    $contact_success = "✅ شكراً لك! تم إرسال رسالتك بنجاح وسنتواصل معك قريباً.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تواصل معنا - Lynex</title>
  
  <style>
    /* 🎨 قاعدة التنسيق الداكن الموحد (الخطوط، الخلفية، الهيدر، الأزرار، الإدخال، الفوتير) */
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');
    :root {
      --main-dark-blue: #0b3c5d; --secondary-dark-blue: #062e46; --accent-color: #3282b8; --cta-color: #f7a000; --light-text: #dbe9f4; --white: #FFFFFF; --dark-text: #111;
    }
    body { margin: 0; font-family: 'Cairo', sans-serif; background: radial-gradient(circle at top left, var(--main-dark-blue), var(--secondary-dark-blue)); color: var(--light-text); text-align: right; min-height: 100vh; overflow-x: hidden; position: relative; padding-top: 70px; }
    header { display: flex; justify-content: space-between; align-items: center; padding: 15px 60px; background: rgba(0, 0, 0, 0.2); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4); position: fixed; top: 0; width: 100%; box-sizing: border-box; z-index: 1000; }
    .logo img { height: 55px; filter: none; }
    nav ul { list-style: none; display: flex; gap: 25px; margin: 0; padding: 0; }
    nav a { text-decoration: none; color: var(--light-text); font-weight: bold; transition: 0.3s; }
    nav a:hover, nav a.active { color: var(--cta-color); }
    .btn, button[type="submit"] { background: var(--cta-color); color: var(--white); padding: 15px 30px; border-radius: 8px; text-decoration: none; transition: 0.3s; font-weight: bold; border: none; cursor: pointer; display: inline-block; font-family: inherit; }
    .btn:hover, button[type="submit"]:hover { background: #d88e00; }
    .btn-outline { border: 2px solid var(--accent-color); color: var(--light-text); background: none; padding: 15px 30px; border-radius: 8px; text-decoration: none; transition: 0.3s; font-weight: bold; display: inline-block; }
    .btn-outline:hover { background: var(--accent-color); color: var(--white); }
    input, textarea { padding: 12px; border-radius: 8px; border: 1px solid #ccc; color: var(--dark-text); background: var(--white); box-sizing: border-box; font-family: inherit; width: 100%; }
    footer { text-align: center; padding: 25px; background: rgba(0, 0, 0, 0.3); color: var(--light-text); margin-top: auto; width: 100%; box-sizing: border-box;}
    .circles div { position: absolute; border-radius: 50%; opacity: 0.2; animation: float 12s infinite ease-in-out; }
    .circles div:nth-child(1) { width: 200px; height: 200px; background: #3282b8; top: 10%; left: 10%; animation-delay: 0s; }
    .circles div:nth-child(2) { width: 350px; height: 350px; background: #1b4965; top: 50%; left: 70%; animation-delay: 4s; }
    .circles div:nth-child(3) { width: 150px; height: 150px; background: #062e46; top: 80%; left: 30%; animation-delay: 8s; }
    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

    /* التنسيقات الخاصة بصفحة contact.php */
    .contact { 
        text-align: center; 
        padding: 60px 20px; 
        flex-grow: 1; /* للسماح للفوتير بالبقاء في الأسفل */
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .contact h2 { color: var(--white); margin-bottom: 30px; }
    .contact form {
      display: flex;
      flex-direction: column;
      width: 100%;
      max-width: 450px; /* لتوسيع النموذج قليلاً */
      margin: 0 auto;
      gap: 15px;
      padding: 30px;
      background: rgba(255, 255, 255, 0.1); 
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    textarea { height: 150px; resize: vertical; }

    .message-box { 
        padding: 15px; margin: 20px auto; border-radius: 8px; max-width: 450px; font-weight: bold; text-align: center; 
    }
    .success-box { background: #e6ffe6; color: #008000; border: 1px solid #008000; }
    .error-box { background: #ffe6e6; color: #d80000; border: 1px solid #d80000; }
  </style>
</head>
<body style="display: flex; flex-direction: column;"> <header>
    <div class="logo"><img src="logo.png" alt="Lynex Logo"></div>
    <nav>
        <ul id="navLinks">
            <li><a href="index.php">الرئيسية</a></li>  
            <li><a href="medaqeq.php">المحلل</a></li> 
            <li><a href="contact.php" class="active">تواصل</a></li> 
            <li><a href="login.php">تسجيل دخول</a></li> 
        </ul>
    </nav>
  </header>

  <section class="contact">
     <h2>تواصل معنا</h2>
    
    <?php if (!empty($contact_success)): ?>
        <div class="message-box success-box"><?php echo $contact_success; ?></div>
    <?php elseif (!empty($contact_error)): ?>
        <div class="message-box error-box"><?php echo $contact_error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="contact.php"> 
      <input type="text" name="contact_name" placeholder="الاسم الكامل" required>
      <input type="email" name="contact_email" placeholder="البريد الإلكتروني" required>
      <textarea name="contact_message" placeholder="اكتب رسالتك هنا..." required></textarea>
      <button type="submit" name="contact_submit">إرسال الرسالة</button>
    </form>
  </section>

  <footer>
    <p>© 2025 Lynex — جميع الحقوق محفوظة</p>
  </footer>

</body>
</html>
<?php ob_end_flush(); ?>