<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <title>من نحن</title>

    <style>
        .credits-section {
            background-color: #2E8B5720;
            padding: 20px;
            border-radius: 15px;
            margin-top: 40px;
            text-align: center;
        }

        .team-section {
            list-style: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }

        .team-member,
        .supervisor {
            background-color: #ffffff;
            padding: 10px;
            border-radius: 10px;
            color: #004d00;
        }

        .supervisor h3 {
            color: #dc3545;
        }

        p {
            text-align: justify;
        }
    </style>
</head>

<body>
    <!-- header -->
    <?php include('layouts/header.php'); ?>

    <div class="container-fluid p-2 text-center">
        <div class="bg-success text-white p-4 rounded-3 shadow-sm bgb">
            <h1 class="display-3">احجز ملعبك الآن!</h1>
            <p class="lead mb-0">انطلق إلى عالم الرياضة والمنافسة</p>
        </div>
        <div class="container-gaol text-center rounded-3 py-0">
            <p>هدفنا تسهيل العناء واختصار الوقت ابحث واحجز الملعب بسهولة</p>
        </div>
    </div>

    <div class="background-image"></div>

    <main>
        <div class="container my-5">
            <h2 class="text-center mb-4">من نحن</h2>
            <p>مرحبًا بكم في <strong>Stadium Booking</strong>، منصتكم الرائدة لحجز الملاعب في المملكة العربية السعودية.
                نحن
                نتخصص في توفير
                منصة سهلة ومريحة لحجز الملاعب الرياضية في مختلف المدن والأحياء، مع تركيز خاص على توفير تجربة مستخدم
                استثنائية.</p>

            <h3>رؤيتنا</h3>
            <p>في <strong>Stadium Booking</strong>، نسعى لأن نكون الوجهة الأولى لكل من يبحث عن ملاعب رياضية عالية
                الجودة.
                رؤيتنا هي تسهيل عملية البحث والحجز لكل الرياضيين والفرق في المملكة العربية السعودية، وتقديم تجربة لا
                تُنسى
                تضمن عودتهم لنا
                مرة بعد أخرى.</p>

            <h3>رسالتنا</h3>
            <p>نحن في <strong>Stadium Booking</strong> نؤمن بأهمية الرياضة ودورها في تحسين جودة الحياة. رسالتنا هي توفير
                إمكانية الوصول السهل والمريح إلى الملاعب الرياضية، بحيث يمكن للجميع - من الهواة إلى المحترفين -
                الاستمتاع
                بالرياضة وممارستها في أفضل البيئات.</p>

            <h3>قيمنا</h3>
            <p>نحن نعتز بقيمنا التي تشمل الالتزام بالجودة، الشفافية في التعاملات، والسعي المستمر نحو الابتكار. فريقنا
                ملتزم
                بتوفير أفضل الخدمات وأكثرها موثوقية في مجال حجز الملاعب الرياضية.</p>

            <h3>فريقنا</h3>
            <p>في <strong>Stadium Booking</strong>، نفخر بفريقنا من الخبراء والمحترفين الذين يعملون بجد لتوفير تجربة حجز
                مثالية. نحن نجمع بين الخبرة في مجال الرياضة، التكنولوجيا، وخدمة العملاء لضمان تلبية جميع احتياجاتكم
                بكفاءة
                وفعالية.</p>

            <h3>اتصل بنا</h3>
            <p>لأية استفسارات أو معلومات إضافية حول خدماتنا، لا تترددوا في <a style="text-decoration: none;"
                    href="contact.php">التواصل معنا</a>. نحن
                هنا
                للإجابة على كل أسئلتكم وتزويدكم بكل ما تحتاجون لتجربة حجز ملاعب سهلة ومريحة.</p>
        </div>
    </main>

    <div class="container credits-section">
        <h3>تم تصميم وبرمجة الموقع بواسطة طلاب المشروع:</h3>
        <ul class="team-section">
            <li class="team-member">أحمد العتيبي</li>
            <li class="team-member">بندر الدبيخي</li>
            <li class="team-member">ثامر الفريح</li>
            <li class="team-member">سامي السويد</li>
            <li class="team-member">محمد العميري</li>
            <li class="team-member">نايف الحربي</li>
        </ul>
        <div class="supervisor">
            <h3>المشرف:</h3>
            <h5>د. عبد الله العجلان</h5>
        </div>
    </div>

    <div class="text-center mt-3"></div>
    <!-- Footer -->
    <?php include('layouts/footer.php'); ?>
</body>

</html>