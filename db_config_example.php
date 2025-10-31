<?php
// 🛑 ملف الإعدادات النموذجي: db_config_example.php 🛑
// ⚠️ لكي يعمل التطبيق، يجب على المستخدم أن يقوم بالتالي:
// 1. إعادة تسمية هذا الملف إلى db_config.php (بعد سحب الكود من GitHub).
// 2. ملء البيانات الصحيحة في السطور التالية.

// إعدادات الاتصال بقاعدة البيانات (استخدم ثوابت PHP)
define('DB_SERVER', 'localhost'); // غالباً 'localhost' أو عنوان IP للخادم
define('DB_USERNAME', 'your_db_username');    // 👈👈 اسم المستخدم الخاص بقاعدة البيانات (اسم مستخدم الاستضافة)
define('DB_PASSWORD', 'your_db_password');    // 👈👈 كلمة المرور الخاصة بقاعدة البيانات
define('DB_NAME', 'lynex_bd');    // اسم قاعدة البيانات (الاسم الذي قمت بإنشائه)

// إنشاء الاتصال (باستخدام MySQLi)
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// التحقق من الاتصال
if($conn->connect_error){
    // عند فشل الاتصال سيظهر الخطأ
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// تعيين ترميز الأحرف إلى UTF-8
$conn->set_charset("utf8mb4");

// 🛑 ملاحظة هامة: لا يوجد وسم إغلاق ?> هنا لتجنب مشكلة Headers