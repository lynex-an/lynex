<?php
// PHP Code (لضمان عمل الـ header بشكل صحيح)
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// يمكن إضافة منطق تسجيل الدخول هنا مستقبلاً
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LYNEX Sanitizer</title>
  
  <style>
    /* 🎨 توحيد التنسيقات العامة */
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');
    
    :root {
      --main-dark-blue: #0b3c5d;       /* كحلي أساسي */
      --secondary-dark-blue: #062e46; /* كحلي غامق للخلفية */
      --accent-color: #3282b8;       /* أزرق التمييز */
      --cta-color: #f7a000;          /* لون زر الدعوة للعمل (برتقالي) */
      --light-text: #dbe9f4;         /* لون النص الفاتح */
      --white: #FFFFFF;
      --card-bg: rgba(255, 255, 255, 0.1); /* خلفية البطاقات الشفافة */
    }

    body { 
        margin: 0; font-family: 'Cairo', sans-serif; 
        background: radial-gradient(circle at top left, var(--main-dark-blue), var(--secondary-dark-blue)); 
        color: var(--light-text); 
        text-align: right; min-height: 100vh; overflow-x: hidden; position: relative; 
        padding-top: 80px; /* لتجنب تداخل المحتوى مع الهيدر الثابت */
    }
    
    /* -------------------- الهيدر والناف بار -------------------- */
    header { 
        display: flex; justify-content: space-between; align-items: center; 
        padding: 15px 60px; 
        background: rgba(6,46,70,0.95); /* كحلي غامق شفاف */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4); 
        position: fixed; top: 0; width: 100%; box-sizing: border-box; z-index: 1000;
        backdrop-filter: blur(6px); /* تأثير ضبابي */
    }
    .logo img { height: 55px; filter: none; }
    nav ul { list-style: none; display: flex; gap: 25px; margin: 0; padding: 0; }
    nav a { text-decoration: none; color: var(--light-text); font-weight: bold; transition: 0.3s; }
    nav a:hover, nav a.active { color: var(--cta-color); }
    
    /* زر البرغر - للجوال */
    .burger { display: none; flex-direction: column; cursor: pointer; gap: 6px; }
    .burger div { width: 25px; height: 3px; background: white; border-radius: 2px; transition: 0.3s; }
    
    /* الأزرار العامة */
    .btn, .btn-outline { 
        text-decoration: none; padding: 12px 25px; border-radius: 10px; font-weight: bold; transition: 0.3s; display: inline-block; 
        border: none; cursor: pointer; font-family: inherit; 
    }
    .btn { background: var(--cta-color); color: var(--white); }
    .btn:hover { background: #d88e00; }
    .btn-outline { border: 2px solid var(--accent-color); color: var(--light-text); background: transparent; }
    .btn-outline:hover { background: var(--accent-color); color: var(--white); }

    /* -------------------- الهيرو (القسم الرئيسي) -------------------- */
    .hero {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 80px 60px 50px 60px;
      max-width: 1200px; margin: 0 auto;
      flex-wrap: wrap;
    }

    .hero-text { max-width: 50%; padding-left: 20px;}
    .hero-text h1 { font-size: 3.5rem; color: var(--white); margin-bottom: 10px; }
    .hero-text span { color: var(--accent-color); }
    .hero-text p { font-size: 1.2rem; line-height: 1.7; color: var(--light-text); margin-bottom: 30px; }
    .hero-buttons { display: flex; gap: 20px; }
    
    .hero-img img {
      width: 420px; height: auto;
      border-radius: 20px;
      box-shadow: 0 5px 25px rgba(0,0,0,0.3);
      animation: floatImg 5s ease-in-out infinite;
    }
    @keyframes floatImg { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

    /* -------------------- قسم عن LYNEX -------------------- */
    .about {
      text-align: center;
      padding: 50px 60px;
      background: rgba(0,0,0,0.1);
      color: var(--light-text);
      box-shadow: inset 0 5px 10px rgba(0,0,0,0.3);
    }
    .about h2 { color: var(--cta-color); font-size: 2rem; margin-bottom: 20px; }
    .about p { font-size: 1.1rem; max-width: 900px; margin: 0 auto; line-height: 1.8; color: var(--light-text); }
    .about strong { color: var(--white); }

    /* -------------------- قسم المزايا (Features) -------------------- */
    .features {
      text-align: center;
      padding: 80px 50px;
    }

    .features h2 {
      color: var(--accent-color);
      font-size: 2.5rem;
      margin-bottom: 50px;
    }

    .feature-list {
      display: grid;
      /* تصميم شبكي بثلاثة أعمدة */
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
      gap: 25px;
      max-width: 1200px;
      margin: auto;
    }

    .feature-item {
      background: var(--card-bg); /* استخدام الخلفية الشفافة */
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.5);
      transition: transform 0.3s, background 0.3s;
      text-align: right;
    }

    .feature-item:hover {
      transform: translateY(-5px);
      background: rgba(255, 255, 255, 0.15);
    }

    .feature-item h3 {
      color: var(--white);
      margin-top: 0;
      margin-bottom: 10px;
      border-bottom: 1px solid var(--accent-color);
      padding-bottom: 8px;
    }

    .feature-item p {
      color: var(--light-text);
      font-size: 15px;
      line-height: 1.6;
    }

    /* -------------------- الفوتير -------------------- */
    footer { 
        text-align: center; 
        padding: 25px; 
        background: rgba(0, 0, 0, 0.3); 
        color: var(--light-text); 
        margin-top: 50px;
        width: 100%;
        box-sizing: border-box;
    }
    .social a { color: var(--cta-color); text-decoration: none; transition: 0.3s; }
    .social a:hover { color: var(--white); }

    /* -------------------- تنسيق الجوال -------------------- */
    @media (max-width: 950px) {
        .hero { flex-direction: column; text-align: center; padding: 60px 20px 30px 20px; }
        .hero-text { max-width: 100%; padding-left: 0; }
        .hero-buttons { justify-content: center; }
        .hero-img { order: -1; margin-bottom: 30px; }
        .hero-img img { width: 90%; max-width: 400px; }
        
        .feature-list { grid-template-columns: repeat(auto-fit, minmax(90%, 1fr)); padding: 0 10px; }
        .feature-item { max-width: 90%; margin: 0 auto; }
        
        header { padding: 15px 20px; }
        nav ul {
            position: fixed;
            top: 0; right: -100%;
            background: rgba(6,46,70,0.98);
            height: 100vh; width: 250px;
            flex-direction: column; justify-content: center; align-items: center; gap: 25px;
            transition: right 0.4s ease;
        }
        nav ul.active { right: 0; }
        .burger { display: flex; }
    }
  </style>
</head>
<body>

<div class="circles">
    </div>

  <header>
    <div class="logo">
       <img src="logo.png" alt="Lynex Logo"> </div>
    <div class="burger" id="burger">
        <div></div><div></div><div></div>
    </div>
    <nav>
        <ul id="navLinks">
            <li><a href="index.php" class="active">الرئيسية</a></li>             <li><a href="medaqeq.php">المحلل</a></li> <li><a href="contact.php">تواصل</a></li>  <li><a href="login.php">تسجيل دخول</a></li> </ul>
    </nav>
</header>

 <section class="hero">
    <div class="hero-text">
      <h1>تنظيف البيانات أصبح أسهل مع LYNEX <span>Sanitizer</span></h1>
      <p>
        أداة LYNEX لمعالجة جودة البيانات هي حلك الذكي للتأكد 
        من أن بياناتك نظيفة، كاملة، وموحدة قبل أي تحليل. قل وداعاً لأخطاء الإدخال والتناقضات.
      </p>
      <div class="hero-buttons">
        <a href="medaqeq.php" class="btn">ابدأ التحليل الآن</a>  
        <a href="login.php" class="btn-outline">تسجيل الدخول</a>       
      </div>
    </div>
    <div class="hero-img">
      <img src="analysis-hero.png" alt="AI Illustration">
    </div>
  </section>

  <section class="about">
    <h2> Lyenx: البيانات مصدر قوة لا تحدٍ </h2>
    <p>
      طوّرنا منصة <strong>Lyenx</strong> لتكون الحل الأمثل، مبنية على منهجية علمية ومدعّمة بتقنيات الذكاء الاصطناعي،
      مع مراعاة التحديات والاحتياجات المحلية في المملكة.<br><br>
      تهدف <strong>Lyenx</strong> إلى تمكين الجهات من بناء قرارات دقيقة، سريعة، وموثوقة لتصبح البيانات مصدر قوة لا تحدٍ.
    </p>
  </section>

  <section class="features">
    <h2>مزايا Lyenx في تحسين جودة البيانات</h2>
    <div class="feature-list">
      
      <div class="feature-item"><h3>Duplicate ID</h3><p>كشف وإزالة السجلات المكررة بناءً على الرقم المعرف لضمان دقة البيانات.</p></div>
      <div class="feature-item"><h3>Duplicate Records</h3><p>توحيد السجلات المكررة بالكامل لتحسين جودة البيانات وتجنب الإفراط في الحساب.</p></div>
      <div class="feature-item"><h3>Handling Randomly Missing Data</h3><p>معالجة البيانات المفقودة بشكل عشوائي بطرق إحصائية لتعزيز دقة التحليل.</p></div>
      
      <div class="feature-item"><h3>Data Missing Not at Random</h3><p>تحليل الأنماط المتكررة لفقد البيانات، وتطبيق تقنيات متقدمة لضمان الاكتمال.</p></div>
      <div class="feature-item"><h3>Non-Uniform Data</h3><p>توحيد البيانات المتناقضة بين المصادر المختلفة، وتوحيد تنسيقات الوحدات والمقاييس.</p></div>
      <div class="feature-item"><h3>Typos</h3><p>تصحيح الأخطاء الإملائية والتقطيعية لضمان جودة البيانات ودقتها.</p></div>
      
      <div class="feature-item"><h3>Outdated Data</h3><p>إزالة البيانات القديمة أو غير المهمة لضمان الحصول على أحدث المعلومات وأكثرها صلة.</p></div>
      <div class="feature-item"><h3>Outliers</h3><p>كشف القيم الشاذة والمتطرفة وتحديد تأثيرها أو معالجتها لتحسين دقة التحليل.</p></div>
      <div class="feature-item"><h3>Irrelevant Data</h3><p>إزالة البيانات غير المهمة أو الأعمدة التي لا قيمة لها للتركيز على الأساسيات.</p></div>
      
      <div class="feature-item"><h3>Un-Normalized Data</h3><p>تحويل البيانات إلى هيكل منظم يسهل عمليات البحث والتحليل ويحسن كفاءة قاعدة البيانات.</p></div>
      <div class="feature-item"><h3>Inconsistent Data</h3><p>معالجة التناقضات والتعارضات المنطقية لضمان تكامل البيانات.</p></div>
      <div class="feature-item"><h3>Incomplete Data</h3><p>تعزيز اكتمال البيانات بتعبئة الحقول المفقودة بذكاء لاتخاذ قرارات دقيقة.</p></div>
      
    </div>
  </section>

  <footer>
    <p>© 2025 Lynex — جميع الحقوق محفوظة</p>
    <div class="social">
      تابعنا على <a href="https://x.com/LynexAi" target="_blank">@LynexAi</a>
    </div>
  </footer>

  <script>
    const burger = document.getElementById('burger');
    const navLinks = document.getElementById('navLinks');

    burger.addEventListener('click', () => {
      navLinks.classList.toggle('active');
    });
  </script>

</body>
</html>