<?php
// 🛑 ملف الاتصال النموذجي: db_connect_example.php 🛑
// ⚠️ لكي يعمل التطبيق، يجب على المستخدم أن يقوم بالتالي:
// 1. إعادة تسمية هذا الملف إلى db_connect.php (بعد سحب الكود من GitHub).
// 2. ملء البيانات الصحيحة في السطور التالية.

// بيانات الاتصال بقاعدة البيانات (استخدم متغيرات PHP)
$servername = "localhost"; // غالباً 'localhost'
$username = "your_db_username"; // 👈👈 اسم المستخدم الخاص بقاعدة البيانات (اسم مستخدم الاستضافة)
$password = "your_db_password"; // 👈👈 كلمة المرور الخاصة بقاعدة البيانات
$dbname = "lynex_bd"; // اسم قاعدة البيانات (الاسم الذي قمت بإنشائه)

// إنشاء الاتصال (باستخدام MySQLi)
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// تعيين ترميز الأحرف إلى UTF-8
$conn->set_charset("utf8mb4");

// 🛑 ملاحظة هامة: لا يوجد وسم إغلاق ?> هنا لتجنب مشكلة Headers already sent