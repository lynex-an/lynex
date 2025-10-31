<?php
// يبدأ الجلسة للوصول إلى بياناتها
session_start();

// تدمير جميع بيانات الجلسة (تسجيل الخروج)
session_unset();
session_destroy();

// إعادة توجيه المستخدم إلى صفحة تسجيل الدخول
header("Location: login.php");
exit();
?>