<?php
// بيانات الاتصال بقاعدة البيانات (XAMPP)
$servername = "localhost";
$username = "root";
$password = ""; // تأكدي من أن هذه فارغة إذا كنت تستخدمين XAMPP بدون كلمة مرور
$dbname = "lynex_bd"; 

// إنشاء الاتصال (باستخدام MySQLi)
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// تعيين ترميز الأحرف إلى UTF-8
$conn->set_charset("utf8mb4");

// 🛑 ملاحظة هامة: لا يوجد وسم إغلاق ?> هنا لتجنب مشكلة Headers already sent