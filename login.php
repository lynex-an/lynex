<?php
session_start();
include 'db_connect.php'; 

$error_message = "";
$success_message = "";

if (isset($_SESSION['user_id'])) {
    header("Location: medaqeq.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (isset($_POST['login_submit'])) {
        $email = $conn->real_escape_string(trim($_POST['email']));
        $password = $_POST['password'];
        $sql = "SELECT user_id, user_name, password_hash FROM users WHERE email = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password_hash'])) {
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['user_name'] = $row['user_name'];
                    $_SESSION['loggedin'] = true;
                    header("Location: medaqeq.php");
                    exit();
                } else {
                    $error_message = "كلمة المرور غير صحيحة.";
                }
            } else {
                $error_message = "لا يوجد حساب مسجل بهذا البريد الإلكتروني.";
            }
            $stmt->close();
        } else {
            $error_message = "خطأ في تجهيز استعلام قاعدة البيانات.";
        }

    } elseif (isset($_POST['signup_submit'])) {
        
        $username = $conn->real_escape_string(trim($_POST['username']));
        $email = $conn->real_escape_string(trim($_POST['email']));
        $password = $_POST['password'];
        
        if (strlen($password) < 6) {
            $error_message = "يجب أن لا تقل كلمة المرور عن 6 أحرف.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $check_sql = "SELECT user_id FROM users WHERE email = ?";
            if ($check_stmt = $conn->prepare($check_sql)) {
                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                $check_stmt->store_result();
                
                if ($check_stmt->num_rows > 0) {
                    $error_message = "هذا البريد الإلكتروني مسجل بالفعل.";
                } else {
                    $insert_sql = "INSERT INTO users (user_name, email, password_hash) VALUES (?, ?, ?)";
                    if ($insert_stmt = $conn->prepare($insert_sql)) {
                        $insert_stmt->bind_param("sss", $username, $email, $password_hash);
                        
                        if ($insert_stmt->execute()) {
                            header("Location: login.php?status=signup_success");
                            exit();
                        } else {
                            $error_message = "خطأ في إنشاء الحساب: " . $insert_stmt->error;
                        }
                        $insert_stmt->close();
                    } else { $error_message = "خطأ في تجهيز استعلام الإنشاء."; }
                }
                $check_stmt->close();
            } else { $error_message = "خطأ في تجهيز استعلام التحقق."; }
        }
    }
}

if (isset($_GET['status']) && $_GET['status'] == 'signup_success') {
    $success_message = "✅ تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول / إنشاء حساب - LYNEX</title>
    
    <style>
        /* 🎨 قاعدة التنسيق الداكن الموحد (الخطوط، الخلفية، الهيدر، الأزرار، الإدخال، الفوتير) */
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');
        :root {
          --main-dark-blue: #0b3c5d; --secondary-dark-blue: #062e46; --accent-color: #3282b8; --cta-color: #f7a000; --light-text: #dbe9f4; --white: #FFFFFF; --dark-text: #111;
        }
        body { 
            margin: 0; font-family: 'Cairo', sans-serif; 
            background: radial-gradient(circle at top left, var(--main-dark-blue), var(--secondary-dark-blue)); 
            color: var(--light-text); 
            text-align: right; min-height: 100vh; overflow-x: hidden; position: relative; 
            display: flex; justify-content: center; align-items: center; 
        }
        /* لا حاجة لتنسيقات الهيدر/الفوتير هنا لأن الصفحة مركزة */
        .btn, button[type="submit"] { background: var(--cta-color); color: var(--white); padding: 15px 30px; border-radius: 8px; text-decoration: none; transition: 0.3s; font-weight: bold; border: none; cursor: pointer; display: inline-block; font-family: inherit; width: 100%; }
        .btn:hover, button[type="submit"]:hover { background: #d88e00; }
        input { 
            padding: 12px; border-radius: 8px; border: 1px solid #ccc; color: var(--dark-text); background: var(--white); box-sizing: border-box; font-family: inherit; width: 100%; 
        }

        /* التنسيقات الخاصة بصفحة login.php */
        .login-box {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
            color: var(--dark-text);
            text-align: center;
            padding: 35px;
            margin: 20px; /* لضمان هامش على الجوال */
        }
        
        .message-box { padding: 15px; margin: 20px auto; border-radius: 8px; max-width: 90%; font-weight: bold; text-align: center; }
        .success-box { background: #e6ffe6; color: #008000; border: 1px solid #008000; }
        .error-box { background: #ffe6e6; color: #d80000; border: 1px solid #d80000; }
        
        h2 { color: var(--main-dark-blue); margin-top: 10px; margin-bottom: 25px; }

        .form-group { margin-bottom: 15px; }

        .tabs { display: flex; justify-content: space-around; margin-bottom: 20px; }
        .tab-button {
            background: none; border: none; color: var(--main-dark-blue); padding: 10px 20px; cursor: pointer; font-size: 1.1em;
            font-weight: bold; border-bottom: 3px solid transparent; transition: border-bottom 0.3s;
        }
        .tab-button.active { border-bottom: 3px solid var(--cta-color); }

        /* إظهار وإخفاء النماذج */
        #loginForm, #signupForm { display: none; text-align: right;}
        .form-container.login #loginForm, .form-container.signup #signupForm { display: block; }
        
        @media (max-width: 500px) {
            .login-box { max-width: 90%; padding: 20px; }
        }
    </style>
</head>
<body>
<div class="login-box">
    
    <?php if (!empty($success_message)): ?>
        <div class="message-box success-box"><?php echo $success_message; ?></div>
    <?php elseif (!empty($error_message)): ?>
        <div class="message-box error-box"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="form-container" id="formContainer">
        
        <div class="tabs">
            <button class="tab-button active" id="loginTab" onclick="switchForm('login')">تسجيل الدخول</button>
            <button class="tab-button" id="signupTab" onclick="switchForm('signup')">إنشاء حساب</button>
        </div>

        <form id="loginForm" method="POST" action="login.php">
            <h2>تسجيل الدخول إلى LYNEX</h2>
            <div class="form-group"><input type="email" name="email" placeholder="البريد الإلكتروني" required></div>
            <div class="form-group"><input type="password" name="password" placeholder="كلمة المرور" required></div>
            <button type="submit" name="login_submit">دخول</button>
            <p style="margin-top: 15px; font-size: 0.9em;"><a href="#" style="color: var(--accent-color); text-decoration: none;">هل نسيت كلمة المرور؟</a></p>
        </form>

        <form id="signupForm" method="POST" action="login.php">
            <h2>إنشاء حساب جديد</h2>
             <div class="form-group"><input type="text" name="username" placeholder="اسم المستخدم" required></div>
            <div class="form-group"><input type="email" name="email" placeholder="البريد الإلكتروني" required></div>
            <div class="form-group"><input type="password" name="password" placeholder="كلمة المرور (لا تقل عن 6 أحرف)" required></div>
            <button type="submit" name="signup_submit">تسجيل</button>
        </form>
    </div>
</div>

<script>
    const formContainer = document.getElementById('formContainer');
    const loginTab = document.getElementById('loginTab');
    const signupTab = document.getElementById('signupTab');

    function switchForm(formType) {
        if (formType === 'login') {
            formContainer.classList.add('login');
            formContainer.classList.remove('signup');
            loginTab.classList.add('active');
            signupTab.classList.remove('active');
            document.getElementById('loginForm').style.display = 'block';
            document.getElementById('signupForm').style.display = 'none';
        } else if (formType === 'signup') {
            formContainer.classList.add('signup');
            formContainer.classList.remove('login');
            loginTab.classList.remove('active');
            signupTab.classList.add('active');
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('signupForm').style.display = 'block';
        }
    }
    
    // منطق تحديد النموذج النشط بناءً على رسائل الخطأ أو النجاح
    let initialForm = 'login';
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'signup_success') {
        initialForm = 'login'; // بعد التسجيل الناجح، اذهب إلى الدخول
    } 
    
    <?php if (isset($_POST['signup_submit']) && !empty($error_message) && $error_message !== "كلمة المرور غير صحيحة.") { ?>
        initialForm = 'signup'; // إذا كان هناك خطأ في التسجيل، ابق على التسجيل
    <?php } ?>
    
    switchForm(initialForm);

    loginTab.addEventListener('click', () => switchForm('login'));
    signupTab.addEventListener('click', () => switchForm('signup'));
</script>
</body>
</html>