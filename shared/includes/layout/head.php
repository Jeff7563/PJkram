<?php
/**
 * head.php - ส่วนหัวของเอกสาร HTML (Meta, CSS, Fonts)
 * @param string $pageTitle ชื่อหัวข้อหน้าเว็บ
 * @param array $extraCss รายการไฟล์ CSS เพิ่มเติม
 */
$pageTitle = $pageTitle ?? 'ระบบจัดการเคลม';
$extraCss = $extraCss ?? [];
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts: Kanit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Core App CSS (Consolidated) -->
    <link rel="stylesheet" href="../shared/assets/css/app-main.css">
    
    <?php foreach ($extraCss as $css): ?>
        <link rel="stylesheet" href="<?php echo $css; ?>">
    <?php endforeach; ?>
</head>
