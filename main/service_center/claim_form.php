<?php
// Simple handler: store uploads to claim_image and append submission JSON to submissions.json
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = __DIR__ . '/claim_image';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $savedFiles = [];
    $fileFields = [
        'imgFullCar' => false,
        'imgSpot' => true,
        'imgPart' => true,
        'parts' => $parts,
        'partsDelivery' => $_POST['partsDelivery'] ?? '',
        'recorder' => $_POST['recorder'] ?? '',
        'files' => $savedFiles
    ];

      // --- MySQL storage (configure below) ---
      $dbConfig = [
        'enabled' => true, // set false to disable DB storage
        'host' => '127.0.0.1',
        'port' => 3306,
        'dbname' => 'service_center',
        'user' => 'dbuser',
        'pass' => 'dbpass',
        'table' => 'claims'
      ];

      if (!empty($dbConfig['enabled'])) {
        try {
          $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $dbConfig['host'], $dbConfig['port'], $dbConfig['dbname']);
          $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

          // create table if not exists
          $create = "CREATE TABLE IF NOT EXISTS `{$dbConfig['table']}` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `created_at` DATETIME DEFAULT NULL,
            `branch` VARCHAR(255) DEFAULT NULL,
            `claimDate` DATE DEFAULT NULL,
            `carType` VARCHAR(50) DEFAULT NULL,
            `carBrand` VARCHAR(100) DEFAULT NULL,
            `vin` VARCHAR(80) DEFAULT NULL,
            `ownerName` VARCHAR(255) DEFAULT NULL,
            `problemDesc` LONGTEXT,
            `inspectMethod` LONGTEXT,
            `inspectCause` LONGTEXT,
            `claimCategory` VARCHAR(100) DEFAULT NULL,
            `repairBranch` TINYINT(1) DEFAULT 0,
            `sendHQ` TINYINT(1) DEFAULT 0,
            `parts` LONGTEXT,
            `partsDelivery` VARCHAR(50) DEFAULT NULL,
            `recorder` VARCHAR(255) DEFAULT NULL,
            `files` LONGTEXT,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
          $pdo->exec($create);

          $stmt = $pdo->prepare("INSERT INTO `{$dbConfig['table']}` (created_at,branch,claimDate,carType,carBrand,vin,ownerName,problemDesc,inspectMethod,inspectCause,claimCategory,repairBranch,sendHQ,parts,partsDelivery,recorder,files) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
          $stmt->execute([
            date('Y-m-d H:i:s'),
            $entry['branch'],
            $entry['claimDate'] ? date('Y-m-d', strtotime($entry['claimDate'])) : null,
            $entry['carType'],
            $entry['carBrand'],
            $entry['vin'],
            $entry['ownerName'],
            $entry['problemDesc'],
            $entry['inspectMethod'],
            $entry['inspectCause'],
            $entry['claimCategory'],
            $entry['repairBranch'] ? 1 : 0,
            $entry['sendHQ'] ? 1 : 0,
            json_encode($entry['parts'], JSON_UNESCAPED_UNICODE),
            $entry['partsDelivery'],
            $entry['recorder'],
            json_encode($entry['files'], JSON_UNESCAPED_UNICODE)
          ]);
          $entry['db_id'] = $pdo->lastInsertId();
        } catch (Exception $e) {
          $entry['db_error'] = $e->getMessage();
        }
      }

      // also keep local JSON log for backup
      $subFile = __DIR__ . '/submissions.json';
      $all = [];
      if (file_exists($subFile)) {
        $txt = file_get_contents($subFile);
        $all = json_decode($txt, true) ?: [];
      }
      $all[] = $entry;
      file_put_contents($subFile, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

      $message = 'บันทึกข้อมูลเรียบร้อย';
}
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>ฟอร์มส่งเคลม</title>
  <link rel="stylesheet" href="css/styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    :root{--orange:#e65100;--orange-600:#ff7a1a;--black:#121212;--muted:#6b6b6b;--bg:#f7f8fb;--surface:#ffffff;--soft-shadow:rgba(16,24,40,0.06);--icon-card-grad:linear-gradient(135deg,#ff7a1a,#ff8a3d)}
    *{box-sizing:border-box}
    body{font-family:'Kanit',Inter,Segoe UI,Roboto,Arial,sans-serif;margin:0;background:linear-gradient(180deg,#fbfbfd,#ffffff);color:var(--black);-webkit-font-smoothing:antialiased;font-size:16px;line-height:1.55}
    .container{max-width:1100px;margin:28px auto;padding:0 18px}
    .header{padding:10px 0}
    h1{color:var(--orange);margin:0;font-size:1.6rem}
    .form-title{display:flex;align-items:center;gap:14px;margin:0}
    /* circular badge for header logo */
    /* circular logo style: thick white ring, strong shadow, centered and slightly overlapping */
    .header{position:relative}
    .header-badge{width:128px;height:128px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;padding:0;background:var(--icon-card-grad);box-shadow:0 30px 80px rgba(16,24,40,0.20);border:10px solid #ffffff;flex-shrink:0;position:relative;z-index:30;background-clip:padding-box}
    .header-badge::after{content:'';position:absolute;inset:0;border-radius:50%;box-shadow:0 8px 20px rgba(255,255,255,0.06) inset}
    /* keep the full logo visible (no cropping) while leaving a small inner margin from the white ring */
    .header-badge img{width:86%;height:86%;max-width:86%;max-height:86%;object-fit:contain;object-position:center;border-radius:50%;display:block}
    /* optional overlap effect: raise badge slightly above header content */
    .header-badge{transform:translateY(-24px)}
    .title-text{display:block}
    .title-text .title{margin:0;color:var(--black);font-size:1.8rem;font-weight:800;letter-spacing:0.2px}
    .subtitle{color:var(--muted);margin-top:6px;font-size:0.98rem;margin:0;opacity:0.92}
    @media (max-width:760px){
      .form-title{flex-direction:column;align-items:center;gap:8px}
      .title-text{text-align:center}
      .subtitle{text-align:center}
    }
    /* reduce space between header and the following container so the first card sits closer */
    header + .container{margin-top:8px}
    .card{background:var(--surface);border-radius:16px;padding:24px;border:1px solid rgba(16,24,40,0.03);box-shadow:0 12px 40px var(--soft-shadow);transition:transform .18s ease,box-shadow .18s ease}
    .card:hover{transform:translateY(-6px);box-shadow:0 24px 60px rgba(16,24,40,0.08)}

    /* Form grid */
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:start}
    /* prevent grid children from forcing overflow; allow them to shrink when needed */
    .form-grid, .form-grid > *, .field, .inline-field, .claim-row, .upload-card, .upload-card .drop-area {min-width:0}
    .form-grid .full{grid-column:1 / -1}
    .field{display:flex;flex-direction:column;gap:6px}
    /* inline-field: label and input on same row for compact fields (e.g., VIN, owner) */
    .inline-field{flex-direction:row;align-items:center;gap:6px}
    /* force inline layout when needed */
    .inline-field.keep-inline{flex-direction:row !important}
    .inline-field.keep-inline .label-text{min-width:160px}
    .inline-field .label-text{margin-bottom:0;min-width:120px;white-space:nowrap;color:var(--muted);font-weight:700}
    /* ensure inputs/selects can shrink and not overflow their containers */
    input[type="text"], input[type="number"], input[type="date"], select, textarea{min-width:0}
    .inline-field .label-text.label-dark{color:var(--black);font-weight:700}
    .inline-field input[type="text"], .inline-field input[type="number"], .inline-field input[type="date"], .inline-field select{flex:1;min-width:0}
    .compact-number{display:inline-flex;align-items:center;gap:8px}
    .compact-number input{width:120px;padding:8px;border-radius:8px;border:1px solid #e9e9e9;font-size:1rem;text-align:right;background:#fff}
    .compact-number .unit{margin-left:6px;color:var(--muted);white-space:nowrap}
    /* icon helper */
    .icon{width:18px;height:18px;vertical-align:middle;margin-right:8px;fill:currentColor}
    .icon.small{width:14px;height:14px;margin-right:6px}
    @media (max-width:880px){ .compact-number input{width:100%;} .inline-field{flex-direction:column;align-items:stretch} .inline-field .label-text{margin-bottom:6px;min-width:auto} }
    .label-text{font-weight:600;color:var(--muted);font-size:0.95rem;margin-bottom:6px}
    /* inline grade & brand fields (keep label and select on same row) */
    .grade-field, .brand-select{display:flex;align-items:center;gap:8px}
    .grade-field .label-text, .brand-select .label-text{margin-bottom:0;display:inline-block;white-space:nowrap}
    .grade-field select{min-width:180px}
    .brand-select select{min-width:200px}
    .label-dark{color:var(--black);font-weight:700}
    .section-legend{font-weight:800;color:#222;margin-bottom:8px;font-size:1rem}
    /* Ensure top-level full sections (fieldset/full) and their fields span the entire card */
    .full .section-legend{display:block}
    .full .field{width:100%;min-width:0}
    .full .label-text{display:block;margin-bottom:8px}
    .full input[type="text"], .full input[type="number"], .full input[type="date"], .full textarea, .full select{width:100%;max-width:100%;box-sizing:border-box}
    /* grouped subfields (two-column rows that should fill the card) */
    .subfield{display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:start}
    .subfield .field{min-width:0}
    .subfield .label-text{display:block;margin-bottom:8px}
    .subfield textarea, .subfield input, .subfield select{width:100%;box-sizing:border-box}
    .note-box{background:#fff6f6;border-left:4px solid var(--icon-card-bg);padding:10px 12px;border-radius:8px;margin-top:10px;color:#3a312f;font-size:0.95rem}
    .note-box ol{margin:6px 0 0 18px;padding:0}
    .note-box li{margin:6px 0}
    input[type="text"],input[type="date"],select,textarea{width:100%;padding:12px;border-radius:10px;border:1px solid rgba(16,24,40,0.06);font-size:1rem;font-family:'Kanit',Inter,Segoe UI,Roboto,Arial,sans-serif;background:linear-gradient(180deg,#ffffff,#fbfbff)}
    input[type="text"]:focus,input[type="date"]:focus,select:focus,textarea:focus{border-color:var(--orange);box-shadow:0 8px 30px rgba(255,106,0,0.06);outline:none}
    select,option{font-family:'Kanit',Inter,Segoe UI,Roboto,Arial,sans-serif}
      input[readonly]{background:#f3f4f6;color:#333;border-color:#e6e6e6;cursor:not-allowed}
    textarea{min-height:96px}

    /* Radio / checkbox rows */
    .radio-row{display:flex;gap:18px;flex-wrap:wrap;align-items:center}
    .radio-row label{display:flex;align-items:center;gap:8px;color:#333}
    /* Use orange for checked controls where supported */
    input[type="checkbox"], input[type="radio"]{accent-color:var(--orange)}
    /* Replace default native controls but keep neutral (dark) border when not selected; orange only when checked */
    input[type="radio"]{
      -webkit-appearance:none;appearance:none;width:18px;height:18px;border-radius:50%;border:2px solid var(--muted);background:transparent;vertical-align:middle;display:inline-block;margin-right:8px;position:relative;
    }
    input[type="radio"]:checked{background:var(--orange);border-color:var(--orange)}
    input[type="radio"]:focus{outline:none;box-shadow:0 0 0 4px rgba(255,106,0,0.08)}
    input[type="checkbox"]{
      -webkit-appearance:none;appearance:none;width:18px;height:18px;border-radius:4px;border:2px solid var(--muted);background:transparent;vertical-align:middle;display:inline-block;margin-right:8px;position:relative;
    }
    input[type="checkbox"]:checked{background:var(--orange);border-color:var(--orange)}
    /* label style when checked (JS will toggle .checked for broad support) */
    label.checked{color:var(--orange);font-weight:700}

    /* Replacement 'ประเภทรถ' pill style */
    .replace-type{display:flex;gap:10px;align-items:center}
    .replace-type label{background:transparent;border:1px solid #f0f0f0;padding:8px 12px;border-radius:10px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;color:#333;font-weight:600}
    .replace-type label:hover{border-color:rgba(0,0,0,0.06)}
    .replace-type label.checked{background:linear-gradient(90deg, rgba(230,110,40,0.12), rgba(230,110,40,0.04));border-color:var(--orange);color:var(--orange);box-shadow:0 8px 20px rgba(230,110,40,0.06)}

    /* Generic pill style for checkbox/radio groups */
    .claim-options label:not(.no-pill), .radio-row label:not(.no-pill), .replace-type label:not(.no-pill){display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:12px;border:1px solid #f2f2f2;background:transparent;cursor:pointer;font-weight:600;color:#333;transition:all .12s ease;position:relative}
    .claim-options label:not(.no-pill):hover, .radio-row label:not(.no-pill):hover{border-color:rgba(0,0,0,0.06)}
    .claim-options label:not(.no-pill).checked, .radio-row label:not(.no-pill).checked, .replace-type label:not(.no-pill).checked{background:linear-gradient(90deg, rgba(230,110,40,0.10), rgba(230,110,40,0.02));border-color:var(--orange);color:var(--orange);box-shadow:0 10px 24px rgba(230,110,40,0.06)}

    /* circular tick indicator inside the pill */
    .claim-options label:not(.no-pill)::before, .radio-row label:not(.no-pill)::before, .replace-type label:not(.no-pill)::before{
      content: '';
      width:18px; height:18px; border-radius:50%; display:inline-block; vertical-align:middle; margin-right:8px; box-sizing:border-box;
      border:2px solid #eee; background:transparent; flex-shrink:0; transition:all .12s ease;
      background-position:center; background-repeat:no-repeat; background-size:10px 10px;
    }
    .claim-options label:not(.no-pill).checked::before, .radio-row label:not(.no-pill).checked::before, .replace-type label:not(.no-pill).checked::before{
      border-color:var(--orange);
      background-image: radial-gradient(circle, var(--orange) 55%, transparent 56%);
    }

    /* hide native small control visuals for radios/checkboxes inside these groups */
    .claim-options input[type="checkbox"], .claim-options input[type="radio"],
    .radio-row input[type="checkbox"], .radio-row input[type="radio"],
    .replace-type input[type="radio"]{
      position: absolute !important; opacity: 0 !important; width:1px; height:1px; margin:0; padding:0;
    }
    /* Claim category row: make select and options inline */
    .claim-row{display:flex;gap:18px;align-items:center;flex-wrap:wrap}
    .claim-row select{min-width:260px;border-radius:8px;padding:10px}
    .claim-options label{margin-right:10px}
    .other-inline{display:flex;align-items:center;gap:8px}
    .other-inline input[type="text"]{display:none;margin-left:8px;padding:8px;border-radius:8px;border:1px solid #e9e9e9}
    .replace-block{display:none;grid-column:1 / -1;margin-top:12px;gap:10px;align-items:center}
    .replace-block .field{flex-direction:row;align-items:center;gap:10px}
    .replace-block .field .label-text{min-width:180px}

    /* Replacement inputs layout */
    .replace-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-top:8px;align-items:center}
    .replace-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:8px;align-items:center}
    .replace-grid .field.inline-field{flex-direction:column;align-items:flex-start;gap:6px}
    .replace-grid .label-text,.replace-grid-2 .label-text{min-width:0;color:var(--muted);font-weight:700}
    .replace-grid input,.replace-grid select,.replace-grid-2 input,.replace-grid-2 select{width:100%;height:44px;padding:8px 10px;border-radius:8px;border:1px solid #ececec;background:#fff;font-size:1rem}
    .replace-grid select{appearance:auto}
    @media (max-width:880px){ .replace-grid{grid-template-columns:1fr; } .replace-grid-2{grid-template-columns:1fr 1fr} }

    /* File inputs and previews */
    input[type="file"]{display:none}
    .preview{display:flex;gap:10px;flex-wrap:wrap;margin-top:8px}
    .preview img{max-width:160px;border-radius:8px;border:1px solid #eee}
    .thumb{position:relative;display:inline-block;border-radius:10px;overflow:hidden;box-shadow:0 8px 30px rgba(16,24,40,0.06);transition:transform .12s ease}
    .thumb:hover{transform:translateY(-4px) scale(1.02)}
    .thumb img{display:block;border-radius:8px;width:140px;height:100px;object-fit:cover}
    .thumb .remove-btn{position:absolute;top:8px;right:8px;background:transparent;color:var(--orange);border:none;border-radius:8px;width:40px;height:40px;padding:0;cursor:pointer;font-size:0.9rem;display:flex;align-items:center;justify-content:center}

    /* lightbox */
    .lightbox{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,0.7);z-index:1200}
    .lightbox.open{display:flex}
      input:focus,textarea:focus,select:focus,
      input:focus-visible,textarea:focus-visible,select:focus-visible{
        outline: none;
        border-color: var(--orange);
        box-shadow: 0 6px 20px rgba(255,106,0,0.06), 0 0 0 4px rgba(255,106,0,0.06);
      }
    .lightbox .imgwrap{max-width:90%;max-height:90%;background:#fff;padding:8px 76px;border-radius:8px;position:relative;display:flex;align-items:center;justify-content:center}

    .lightbox .imgframe{position:relative;display:block}
    .lightbox img{max-width:100%;max-height:80vh;display:block;border-radius:6px}

    /* side navigation buttons overlayed at image sides */
    .lightbox .nav{position:absolute;top:50%;transform:translateY(-50%);width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.92);border:none;display:flex;align-items:center;justify-content:center;font-size:28px;cursor:pointer;box-shadow:0 10px 30px rgba(0,0,0,0.18);z-index:1300}
    /* place buttons outside the image frame */
    .lightbox .nav.prev{left:calc(-56px - 18px)}
    .lightbox .nav.next{right:calc(-56px - 18px)}

    /* responsive: on small screens keep buttons overlayed inside the frame */
    @media (max-width:740px){
      .lightbox .imgwrap{padding:8px}
      .lightbox .nav.prev{left:12px}
      .lightbox .nav.next{right:12px}
    }

    .lightbox .counter{position:absolute;bottom:10px;left:50%;transform:translateX(-50%);background:rgba(0,0,0,0.48);color:#fff;padding:6px 10px;border-radius:999px;font-weight:700;font-size:0.9rem}

    .lightbox .close{position:absolute;top:18px;right:18px;background:#fff;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 6px 18px rgba(0,0,0,0.12)}
    fieldset.images{border:0;padding:0;margin:0}
    fieldset.images legend{font-weight:800;color:#222;margin-bottom:6px}

    /* Image gallery uploader */
    .image-uploader{margin-top:6px}
    .attach-all{display:inline-flex;align-items:center;gap:10px;padding:9px 14px;background:#06a84b;color:#fff;border-radius:8px;border:none;cursor:pointer;font-weight:700;margin-bottom:12px}
    /* allow cards to size naturally to content to avoid inner scrollbars */
    .image-gallery{display:grid;grid-template-columns:repeat(2,1fr);gap:22px;justify-items:stretch;grid-auto-rows:auto;align-items:start}
    .upload-card{background:#fff;border:1px solid #e8e8e8;border-radius:10px;min-height:160px;display:flex;align-items:center;justify-content:space-between;padding:14px;cursor:pointer;position:relative;overflow:visible}
    .upload-card .drop-area{flex:1;height:100%;display:flex;align-items:flex-start;justify-content:flex-start;flex-direction:column;text-align:left;padding:14px;box-sizing:border-box;position:relative}
    .upload-card.dragover{outline:3px dashed rgba(0,0,0,0.08);background:linear-gradient(180deg,#fafafa,#fff)}
    .upload-card:hover{transform:translateY(-4px);box-shadow:0 10px 30px rgba(13,13,13,0.06);transition:transform .14s ease,box-shadow .14s ease}
    .upload-placeholder{color:var(--black);font-weight:800;font-size:1.02rem;opacity:0.98;display:flex;align-items:center;gap:12px}
    .upload-placeholder .icon{width:48px;height:48px;padding:8px;border-radius:12px;background:var(--icon-card-grad);color:#fff;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(255,122,50,0.12)}
    .upload-placeholder .icon svg{width:22px;height:22px;fill:#fff}
    .upload-hint{color:var(--muted);font-size:0.9rem;margin-top:6px}
    /* modern floating attach-count badge (anchored to drop-area to avoid covering previews) */
    /* attach-count shown under the title as parenthetical red text */
    .upload-card .drop-area .attach-count{display:none;position:static;top:auto;right:auto;background:transparent;color:#c62828;padding:0;margin-top:8px;font-weight:700;font-size:0.95rem;border-radius:0;box-shadow:none;backdrop-filter:none;transition:opacity .12s ease,transform .12s ease}
    .upload-card .drop-area .attach-count[data-show="true"]{display:block;opacity:1}
    .upload-card .drop-area .attach-count[data-animate="pulse"]{transform:scale(1.02)}
    /* preview container constrained to card and won't overflow */
    .upload-card .preview{width:320px;max-width:45%;height:auto;display:flex;gap:8px;align-items:center;justify-content:center;flex-shrink:0;box-sizing:border-box;padding:6px}
    .upload-card .preview{border-radius:8px;overflow:hidden;background:#fff}
    .upload-card .preview img{display:block;width:100%;height:100%;max-height:100%;object-fit:cover}
    /* when multiple images attached make previews smaller */
    .upload-card.multi .preview{width:180px}
    .upload-card.multi .preview img{width:100%;height:100%;object-fit:cover;border-radius:6px}
    /* hide only the small hint when there is a preview; keep the title visible */
    .upload-card.has-preview .drop-area .upload-hint{opacity:0;transform:translateX(-6px);pointer-events:none}
    @media (max-width:1000px){.image-gallery{grid-template-columns:repeat(2,1fr)}}
    @media (max-width:760px){.image-gallery{grid-template-columns:1fr} .upload-card{flex-direction:column;align-items:center} .upload-card .drop-area{text-align:center;align-items:center} .upload-card .preview{width:100%;margin-top:10px}}

    /* Parts table */
    table#partsTable{width:100%;border-collapse:collapse;margin-top:8px;table-layout:fixed}
    table#partsTable th,table#partsTable td{text-align:left;padding:8px;border-bottom:1px solid #f5f5f5;vertical-align:middle}
    table#partsTable thead th{background:#fafafa;font-weight:700;padding:10px 8px;border-bottom:2px solid #f0f0f0}
    table#partsTable td{padding:6px 8px}
    table#partsTable input{width:100%;box-sizing:border-box;padding:8px;border-radius:8px;border:1px solid #e9e9e9;height:44px;background:#fff}
    table#partsTable input[type="number"]{text-align:right}
    table#partsTable .idx{width:48px;text-align:center}
    table#partsTable button.removePart{background:transparent;color:var(--orange);border:none;border-radius:10px;width:40px;height:40px;padding:0;display:flex;align-items:center;justify-content:center;cursor:pointer}

    /* Delete icons: no background, icon colored with primary orange */
    .thumb .remove-btn svg, table#partsTable button.removePart svg{fill:var(--orange);width:20px;height:20px}
    /* explicit column widths for balanced layout */
    table#partsTable thead th:nth-child(1){width:48px}
    table#partsTable thead th:nth-child(2){width:140px}
    table#partsTable thead th:nth-child(3){width:auto}
    table#partsTable thead th:nth-child(4){width:96px}
    table#partsTable thead th:nth-child(5){width:120px}
    table#partsTable thead th:nth-child(6){width:160px}
    table#partsTable thead th:nth-child(7){width:72px}
    /* green add button */
    .btn.add{background:linear-gradient(180deg,#06b957,#049a44);color:#fff;border:none;border-radius:10px;padding:10px 14px;box-shadow:0 8px 18px rgba(6,168,75,0.14);display:inline-flex;align-items:center;gap:8px}
    /* hide small label elements inside parts table and ensure inputs fill columns */
    table#partsTable .label-text{display:none}
    table#partsTable td{padding:6px 6px}
    table#partsTable td > .field{display:block}
    table#partsTable td > input, table#partsTable td input{width:100%;border-radius:8px}
    table#partsTable td .btn.small{margin:0}

    /* Actions */
    .actions{display:flex;gap:12px;justify-content:flex-end;margin-top:16px}
    .btn{padding:10px 14px;border-radius:8px;border:1px solid #e6e6e6;background:#fff;font-weight:600;font-size:1rem}
    .btn.small{padding:8px 10px;font-size:0.95rem}

    /* responsive table wrapper */
    /* allow horizontal scroll for wide tables but avoid vertical inner scrollbars */
    .table-responsive{width:100%;overflow-x:auto;overflow-y:visible;-webkit-overflow-scrolling:touch}
    table#partsTable{min-width:720px}
    .btn.primary{background:var(--orange);color:#fff;border-color:var(--orange);box-shadow:0 6px 18px rgba(255,106,0,0.12)}

    /* Responsive */
    @media (max-width:880px){
      .form-grid{grid-template-columns:1fr;gap:12px}
      .label-text{font-size:0.95rem}
      .container{padding:0 12px}
      .inline-field{flex-direction:column;align-items:stretch}
      .inline-field .label-text{margin-bottom:6px;min-width:auto}
    }
    @media (max-width:760px){
      /* Make parts table stacked on small screens for easier data entry */
      .table-responsive{overflow-x:auto;overflow-y:visible}
      table#partsTable{min-width:0;border:0}
      table#partsTable thead{display:none}
      table#partsTable tbody tr{display:block;border-bottom:1px solid #f5f5f5;padding:10px 0;margin-bottom:8px}
      table#partsTable td{display:flex;flex-direction:column;padding:6px 0;border-bottom:none}
      table#partsTable td .label-text{display:block;color:var(--muted);font-size:0.95rem;margin-bottom:6px}
      table#partsTable td input{height:44px}
      table#partsTable .idx{display:none}
      table#partsTable td .btn.small{align-self:flex-end;margin-top:8px}

      /* Mobile-specific UX improvements */
      body{font-size:16px}
      .container{padding:0 12px}
      .card{padding:18px}
      h1,h2,h3{font-size:1.1rem}
      .form-title{gap:10px}
      .header-badge{width:88px;height:88px}
      .subfield{grid-template-columns:1fr}
      /* ensure full sections' inputs fill card on mobile */
      .full .field{width:100%}
      .full input[type="text"], .full textarea, .full select{width:100%}
      /* ensure subfield controls take full width and labels sit above controls */
      .subfield .label-text{display:block}
      .subfield .field{width:100%}
      .subfield textarea{min-height:88px}

      /* Stack upload card contents; make preview full-width below title */
      .upload-card{flex-direction:column;align-items:stretch;padding:12px}
      .upload-card .drop-area{text-align:center;align-items:center;padding:12px}
      .upload-card .preview{width:100%;max-width:100%;height:auto;margin-top:10px;justify-content:flex-start}
      .upload-card .preview .thumb img{width:120px;height:86px}
      .upload-card.multi .preview{width:100%}

      /* Make form inputs and buttons larger for touch */
      input[type="text"], input[type="date"], select, textarea{padding:14px;font-size:1rem}
      .btn, .btn.small{width:100%;display:block}
      .actions{flex-direction:column;align-items:stretch;gap:10px}

      /* increase remove button target for thumbnails */
      .thumb .remove-btn{width:44px;height:44px;top:6px;right:6px}

      /* ensure lightbox nav inside on mobile (already handled) and make arrows larger */
      .lightbox .nav{width:48px;height:48px;font-size:24px}

      /* ensure labels and hints are readable */
      .upload-placeholder{font-size:1rem}
      .upload-hint{font-size:0.95rem}
    }
    /* Extra mobile adjustments to ensure fields fit within the card and avoid horizontal overflow */
    @media (max-width:880px){
      .inline-field.keep-inline{flex-direction:column !important}
      .claim-row select, .brand-select select, .grade-field select{min-width:0;width:100%}
      input[type="text"], input[type="number"], input[type="date"], select, textarea{max-width:100%;box-sizing:border-box}
    }
    @media (max-width:760px){
      .claim-row{flex-direction:column;align-items:stretch;gap:10px}
      .claim-row select{min-width:0;width:100%}
      .replace-grid, .replace-grid-2{grid-template-columns:1fr}
      .upload-card, .upload-card .preview, .upload-card .drop-area{width:100%;box-sizing:border-box}
      .upload-card .preview{justify-content:flex-start}
      input[type="text"], input[type="number"], input[type="date"], select, textarea{max-width:100%;box-sizing:border-box}
    }
  </style>
</head>
<body>
  <header class="header">
    <div class="container">
      <div class="form-title">
        <span class="header-badge" aria-hidden="true"><img src="https://i.ibb.co/svxDp4Y7/image.png" alt="UKH Logo"></span>
        <div class="title-text">
          <h2 class="title">ฟอร์มส่งเคลม</h2>
          <p class="subtitle">คำแนะนำการใช้งาน: กรุณากรอกข้อมูลและแนบรูปประกอบเพื่อใช้เป็นหลักฐานในการตรวจสอบเคลม</p>
        </div>
      </div>
    </div>
  </header>

  <main class="container">
    <?php if ($message): ?>
      <div class="card" style="margin-bottom:12px;padding:10px;background:linear-gradient(90deg,#ff6a00,#ff8f3d);color:#fff;border-radius:8px"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- SVG sprite for small UI icons -->
    <svg style="display:none" aria-hidden="true">
      <symbol id="icon-claim" viewBox="0 0 24 24"><path d="M6 2h9a2 2 0 0 1 2 2v3h-2V4H6v16h6v2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/></symbol>
      <symbol id="icon-image" viewBox="0 0 24 24">
        <rect x="2" y="5" width="20" height="14" rx="3"/>
        <path d="M8 13.5l3-3 4 5H6l2-2.5z" fill="#fff" opacity="0.08"/>
        <circle cx="12" cy="12" r="3.2"/>
      </symbol>
      <symbol id="icon-modern" viewBox="0 0 24 24">
        <path d="M4 7a2 2 0 0 1 2-2h2.2l1-1h5.6l1 1H18a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7z"/>
        <circle cx="12" cy="13" r="3.2"/>
        <rect x="7.5" y="8.5" width="2.5" height="1.8" rx="0.4"/>
      </symbol>
      <symbol id="icon-upload" viewBox="0 0 24 24"><path d="M12 3l4 4h-3v6h-2V7H8l4-4zM5 19h14v2H5v-2z"/></symbol>
      <symbol id="icon-add" viewBox="0 0 24 24"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z"/></symbol>
      <symbol id="icon-delete" viewBox="0 0 24 24"><path d="M6 7h12v13a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7zm3-4h6v2H9V3z"/></symbol>
      <symbol id="icon-close" viewBox="0 0 24 24"><path d="M18.3 5.7L12 12l6.3 6.3-1.4 1.4L10.6 13.4 4.3 19.7 2.9 18.3 9.2 12 2.9 5.7 4.3 4.3 10.6 10.6 16.9 4.3z"/></symbol>
    </svg>

    <form id="claimForm" class="card" method="post" enctype="multipart/form-data">
      <section class="form-grid">
        <label class="field inline-field keep-inline"> <span class="label-text label-dark">สาขา :</span>
          <select id="branch" name="branch" required>
            <option value="">-- เลือกสาขา --</option>
            <option>สาขา กรุงเทพฯ</option>
            <option>สาขา เชียงใหม่</option>
            <option>สาขา ขอนแก่น</option>
          </select>
        </label>

          <label class="field inline-field keep-inline"> <span class="label-text label-dark">วันที่ส่งเคลม : </span>
            <input type="date" id="claimDate" name="claimDate" value="<?php echo date('Y-m-d'); ?>" readonly required>
          </label>

        <div class="radio-group" style="grid-column:span 2"> <span class="label-text label-dark">ประเภทรถ : </span>
          <div class="radio-row">
            <label><input type="radio" name="carType" value="new" checked> รถใหม่</label>
            <label><input type="radio" name="carType" value="used"> รถมือสอง</label>
            <label class="brand-select no-pill"><span class="label-text label-dark">ยี่ห้อ :</span>
              <select id="carBrand" name="carBrand">
                <option value="">-- เลือกยี่ห้อ --</option>
                <option>Honda</option>
                <option>Yamaha</option>
                <option>Vespa</option>
              </select>
            </label>
            <label class="grade-field no-pill" style="display:none;align-items:center;">
              <span class="label-text label-dark" style="margin-right:8px;margin-bottom:0">เกรด :</span>
              <select id="usedGrade" name="usedGrade">
                <option value="">เลือกเกรด</option>
                <option value="A_premium">A พรีเมี่ยม</option>
                <option value="A_w6">A รับประกันเครื่องยน 6 เดือน</option>
                <option value="C_w1">C รับประกันเครื่องยนต์ 1 เดือน</option>
                <option value="C_as_is">C ตามสภาพไม่รับประกัน</option>
              </select>
            </label>
          </div>
        </div>

        <label class="field inline-field keep-inline"> <span class="label-text label-dark"> หมายเลขตัวถัง : </span>
          <input type="text" id="vin" name="VIN Number" placeholder="VIN Number" required>
        </label>

        <label class="field inline-field keep-inline"> <span class="label-text label-dark"> ชื่อ-นามสกุล(ผู้ซื้อ) :</span>
          <input type="text" id="ownerName" name="ownerName" placeholder="ชื่อ นามสกุล" required>
        </label>

        <div class="full">
          <div class="section-legend">รายละเอียดปัญหาที่ลูกค้าแจ้ง :</div>
          <label class="field">
            <textarea id="problemDesc" name="problemDesc" rows="4" placeholder="อธิบายปัญหาที่ลูกค้าแจ้ง" required></textarea>
          </label>
        </div>

        <div class="full">
          <div class="section-legend">ผลการตรวจเช็คปัญหา :</div>
          <div class="subfield">
            <label class="field"> <span class="label-text">วิธีตรวจเช็ค :</span>
              <textarea id="inspectMethod" name="inspectMethod" rows="2" placeholder="วิธีตรวจเช็ค"></textarea>
            </label>
            <label class="field"> <span class="label-text">สาเหตุของปัญหา :</span>
              <textarea id="inspectCause" name="inspectCause" rows="2" placeholder="สาเหตุของปัญหา"></textarea>
            </label>
          </div>
          <div class="note-box" aria-live="polite">
            <strong>***หมายเหตุ : </strong>
            <ol>
              <li>รถมือสองมีปัญหาปรึกษาช่างมือสอง เบอร์โทรศัพท์ พี่สีเมือง 061-0190011 พี่บัว 093-3197117  หรือ 042-71135 ต่อ 201</li>
              <li>รถใหม่มีปัญหาปรึกษาศูนย์บริการ Honda 086-4594656 Yamaha 086-4550614 Vespa 099-1285556</li>
            </ol>
          </div>
        </div>

        <fieldset class="full images">
          <legend>แนบรูปภาพปัญหา :</legend>
          <div class="image-uploader">
            <div class="image-gallery" id="imageGallery">
              <div class="upload-card" data-field="imgFullCar">
                <div class="drop-area">
                  <div class="upload-placeholder"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-modern"></use></svg>ภาพรถทั้งคัน</div>
                  <div class="upload-hint">คลิกหรือวางรูปที่นี่</div>
                  <span class="attach-count" aria-hidden="true">0</span>
                </div>
                <input type="file" id="imgFullCar" name="imgFullCar" accept="image/*">
                <div class="preview" data-target="imgFullCar"></div>
              </div>

              <div class="upload-card" data-field="imgSpot">
                <div class="drop-area">
                  <div class="upload-placeholder"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-modern"></use></svg>ภาพจุดที่เกิดปัญหา</div>
                  <div class="upload-hint">รองรับหลายรูป</div>
                  <span class="attach-count" aria-hidden="true">0</span>
                </div>
                <input type="file" id="imgSpot" name="imgSpot[]" accept="image/*" multiple>
                <div class="preview" data-target="imgSpot"></div>
              </div>

              <div class="upload-card" data-field="imgPart">
                <div class="drop-area">
                  <div class="upload-placeholder"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-modern"></use></svg>ภาพชิ้นส่วนที่เกิดความเสียหาย</div>
                  <div class="upload-hint">รองรับหลายรูป</div>
                  <span class="attach-count" aria-hidden="true">0</span>
                </div>
                <input type="file" id="imgPart" name="imgPart[]" accept="image/*" multiple>
                <div class="preview" data-target="imgPart"></div>
              </div>

              <div class="upload-card" data-field="imgWarranty">
                <div class="drop-area">
                  <div class="upload-placeholder"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-modern"></use></svg>ภาพสมุดรับประกันที่มีประวัติ</div>
                  <div class="upload-hint">คลิกหรือวางรูปที่นี่</div>
                  <span class="attach-count" aria-hidden="true">0</span>
                </div>
                <input type="file" id="imgWarranty" name="imgWarranty" accept="image/*">
                <div class="preview" data-target="imgWarranty"></div>
              </div>

              <div class="upload-card" data-field="imgOdometer">
                <div class="drop-area">
                  <div class="upload-placeholder"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-modern"></use></svg>ภาพเลขไมล์</div>
                  <div class="upload-hint">คลิกหรือวางรูปที่นี่</div>
                  <span class="attach-count" aria-hidden="true">0</span>
                </div>
                <input type="file" id="imgOdometer" name="imgOdometer" accept="image/*">
                <div class="preview" data-target="imgOdometer"></div>
              </div>

              <div class="upload-card" data-field="imgEstimate">
                <div class="drop-area">
                  <div class="upload-placeholder"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-modern"></use></svg>ภาพใบประเมินรายการอะไหล่</div>
                  <div class="upload-hint">คลิกหรือวางรูปที่นี่</div>
                  <span class="attach-count" aria-hidden="true">0</span>
                </div>
                <input type="file" id="imgEstimate" name="imgEstimate" accept="image/*">
                <div class="preview" data-target="imgEstimate"></div>
              </div>

            </div>
          </div>
        </fieldset>

        <fieldset class="full">
          <legend>ประเภทการเคลม :</legend>
          <div class="claim-row">
            <label class="field" style="margin:0">
              <select id="claimCategory" name="claimCategory">
                <option value="">-- เลือกประเภทการเคลม --</option>
                <option value="pre-sale">เคลมรถก่อนขาย</option>
                <option value="technical">เคลมปัญหาทางเทคนิค</option>
                <option value="customer-sale">เคลมรถลูกค้า</option>
              </select>
            </label>

            <div class="claim-options">
              <label><input type="radio" name="claimAction" id="claim_repair" value="repairBranch"> ซ่อมที่สาขา</label>
              <label><input type="radio" name="claimAction" id="claim_send" value="sendHQ"> ส่งซ่อมที่สนญ.</label>
              <label><input type="radio" name="claimAction" id="claim_replace" value="replaceVehicle"> เปลี่ยนคัน</label>
              <label class="other-inline"><input type="radio" name="claimAction" id="claim_other" value="other"> อื่นๆ <input type="text" id="claimOtherText" name="claimOtherText" placeholder="ระบุอื่นๆ" style="display:none"></label>
            </div>
          </div>
        </fieldset>

        <section id="partsSection" class="full parts" style="display:none">
          <h3>ระบุรายการอะไหล่ ที่ต้องการเคลม/จำนวน</h3>
          <div class="table-responsive">
          <table id="partsTable">
            <thead>
              <tr>
                <th style="width:48px">ลำดับ</th>
                <th style="width:140px">รหัสอะไหล่</th>
                <th>ชื่ออะไหล่</th>
                <th style="width:96px">จำนวน</th>
                <th style="width:120px">ราคา</th>
                <th style="width:160px">หมายเหตุ</th>
                <th style="width:72px">จัดการ</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
          </div>
          <div class="parts-actions">
            <button type="button" id="addPart" class="btn small add"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-add"></use></svg>เพิ่มรายการ</button>
          </div>
        </section>

        <fieldset id="partsDeliverySection" class="full" style="display:none">
          <legend>ประเภทการส่ง อะไหล่</legend>
          <div class="radio-row">
            <label><input type="radio" name="partsDelivery" value="in_stock" checked> ใช้อะไหล่ ที่มีในสต็อกสาขา</label>
            <label><input type="radio" name="partsDelivery" value="wait_hq"> รอส่งอะไหล่ จากสนญ.</label>
            <label><input type="radio" name="partsDelivery" value="buy_outside"> ซื้ออะไหล่ร้านนอก</label>
            <label class="other-inline"><input type="radio" name="partsDelivery" id="partsDeliveryOtherRadio" value="other"> อื่นๆ <input type="text" id="partsDeliveryOtherText" name="partsDeliveryOtherText" placeholder="ระบุอื่นๆ"></label>
          </div>
        </fieldset>

        <!-- Replacement details show here when 'เปลี่ยนคัน' is checked -->
        <div class="replace-block card" id="replaceBlock" style="display:none;padding:14px">
          <div class="section-legend">รายละเอียดการเปลี่ยนคันใหม่</div>
          <div class="field inline-field"><span class="label-text">รถคันเก่า : คงเหลือเงินดาวน์</span>
            <div class="compact-number"><input type="number" name="old_down_balance" placeholder="0.00" step="0.01" min="0" /><span class="unit">บาท</span></div>
          </div>
          <fieldset id="replaceDetails" class="full">
           <legend>รายละเอียดรถคันใหม่</legend>
            <div class="field inline-field"><span class="label-text">รถคันใหม่ : คงเหลือเงินดาวน์</span>
              <div class="compact-number"><input type="number" name="new_down_balance" placeholder="0.00" step="0.01" min="0" /><span class="unit">บาท</span></div>
                        <div class="field inline-field"><span class="label-text">ประเภทรถ</span>
              <div class="replace-type" style="display:flex;gap:12px;align-items:center">
                <label><input type="radio" name="replaceType" value="new"> รถใหม่</label>
                <label><input type="radio" name="replaceType" value="used"> รถมือสอง</label>
              </div>
          </div>
            </div>
            <div class="replace-grid">
              <label class="field inline-field"><span class="label-text">ยี่ห้อ</span>
                <select name="replace_brand" id="replaceBrand">
                  <option value="">-- เลือกยี่ห้อ --</option>
                  <option>Honda</option>
                  <option>Yamaha</option>
                  <option>Vespa</option>
                </select>
              </label>
              <label class="field inline-field"><span class="label-text">รุ่น</span><input type="text" name="replace_model" placeholder="รุ่น"></label>
              <label class="field inline-field"><span class="label-text">สี</span><input type="text" name="replace_color" placeholder="สี"></label>
            </div>

            <div class="replace-grid-2">
              <label class="field inline-field"><span class="label-text">เลขตัวถัง</span><input type="text" name="replace_vin" placeholder="เลขตัวถัง / VIN"></label>
              <label class="field inline-field"><span class="label-text">วันที่รับรถ</span><input type="date" name="replace_receive_date"></label>
            </div>
          </fieldset>
          <div style="margin-top:8px">
            <label class="field full"><span class="label-text">สาเหตุที่เปลี่ยนคัน</span>
              <input type="text" name="replace_reason" placeholder="ระบุสาเหตุการเปลี่ยนคัน">
            </label>
          </div>

          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-top:8px">
            <label class="field inline-field"><span class="label-text">ผู้อนุมัติ</span><input type="text" name="replace_approver" placeholder="ชื่อผู้อนุมัติ"></label>
            <label class="field inline-field"><span class="label-text">วันที่อนุมัติ</span><input type="date" name="replace_approve_date"></label>
          </div>

          <div style="margin-top:12px">
            <div style="margin-top:8px;color:#c00;font-size:0.9rem">
              <div>***หมายเหตุ : </div>
              <div>1. ลูกค้าแจ้งเปลี่ยนคัน ส่งให้สินเชื่อพร้อมใบอนุมัติทุกครั้งที่มีการเปลี่ยน/ตัวจริงแนบมากับสัญญาส่งให้บัญชี</div>
              <div style="margin-top:6px;color:#c00;font-size:0.85rem">2. สินเชื่อเช็คประกันรถหาย / ทะเบียนแก้ไข พ.ร.บ.-ทะเบียน / บริหารสต็อก ตัดแลกเปลี่ยน / ธุรการสินเชื่อ ตรวจรอบการเปิดขาย</div>
            </div>
          </div>
        </div>

        <label class="field"> <span class="label-text">ผู้บันทึกส่งเคลม</span>
          <input type="text" id="recorder" name="recorder" placeholder="ชื่อผู้บันทึก" required>
        </label>

      </section>

      <div class="actions">
        <button type="submit" class="btn primary">บันทึกการส่งเคลม</button>
        <button type="reset" class="btn">รีเซ็ต</button>
      </div>

      <div id="result" class="result" role="status" aria-live="polite"></div>
    </form>
  </main>

  <script src="js/app.js"></script>
  <script>
    (function(){
      // set claimDate value from placeholder if empty so user can edit it
      const _claimDate = document.getElementById('claimDate');
      if(_claimDate && !_claimDate.value){ _claimDate.value = _claimDate.placeholder || '<?php echo date('Y-m-d'); ?>'; }

      const gallery = document.getElementById('imageGallery');
      const form = document.getElementById('claimForm');
      const resultBox = document.getElementById('result');
      if(!gallery || !form) return;

      // show grade select only for used cars and keep it inline with brand
      const gradeField = document.querySelector('.grade-field');
      function updateGradeVisibility(){
        const used = !!document.querySelector('input[name="carType"]:checked') && document.querySelector('input[name="carType"]:checked').value === 'used';
        if(gradeField) gradeField.style.display = used ? 'flex' : 'none';
      }
      // listen for carType changes
      document.querySelectorAll('input[name="carType"]').forEach(r => r.addEventListener('change', updateGradeVisibility));
      // init visibility
      updateGradeVisibility();

      // Claim action: single-select radios (repair / send / replace / other)
      const claimActionRadios = document.querySelectorAll('input[name="claimAction"]');
      const replaceBlock = document.getElementById('replaceBlock');
      const partsSection = document.getElementById('partsSection');
      const claimOtherTextInit = document.getElementById('claimOtherText');

      function updatePartsVisibility(){
        if(!partsSection) return;
        const sel = document.querySelector('input[name="claimAction"]:checked');
        const val = sel ? sel.value : '';
        const show = val === 'repairBranch' || val === 'sendHQ';
        partsSection.style.display = show ? 'block' : 'none';
        const partsDeliverySection = document.getElementById('partsDeliverySection');
        if(partsDeliverySection) partsDeliverySection.style.display = show ? 'block' : 'none';
      }

      function updateClaimActionVisibility(){
        const sel = document.querySelector('input[name="claimAction"]:checked');
        const val = sel ? sel.value : '';
        if(replaceBlock) replaceBlock.style.display = val === 'replaceVehicle' ? 'grid' : 'none';
        if(claimOtherTextInit) { claimOtherTextInit.style.display = val === 'other' ? 'inline-block' : 'none'; if(val !== 'other') claimOtherTextInit.value = ''; }
      }

      claimActionRadios.forEach(r=> r.addEventListener('change', ()=>{ updatePartsVisibility(); updateClaimActionVisibility(); updateCheckedClasses(); }));
      // init
      updatePartsVisibility(); updateClaimActionVisibility();

      // show an 'other' input when partsDelivery = other is selected
      const partsDeliveryOtherText = document.getElementById('partsDeliveryOtherText');
      const partsDeliveryRadios = document.querySelectorAll('input[name="partsDelivery"]');
      if(partsDeliveryRadios && partsDeliveryOtherText){
        function updatePartsDeliveryOther(){
          const sel = document.querySelector('input[name="partsDelivery"]:checked');
          const other = sel && sel.value === 'other';
          partsDeliveryOtherText.style.display = other ? 'inline-block' : 'none';
          if(!other) partsDeliveryOtherText.value = '';
        }
        partsDeliveryRadios.forEach(r=> r.addEventListener('change', updatePartsDeliveryOther));
        updatePartsDeliveryOther();
      }

      // Add checked-class toggling for checkboxes/radios so labels can be styled
      function updateCheckedClasses(){
        Array.from(form.querySelectorAll('input[type="checkbox"], input[type="radio"]')).forEach(inp => {
          const lab = inp.closest('label');
          if(!lab) return;
          if(inp.checked) lab.classList.add('checked'); else lab.classList.remove('checked');
        });
      }
      // Attach listeners
      Array.from(form.querySelectorAll('input[type="checkbox"], input[type="radio"]')).forEach(inp => inp.addEventListener('change', updateCheckedClasses));
      // init
      updateCheckedClasses();

      // Parts table dynamic rows
      const partsTableBody = document.querySelector('#partsTable tbody');
      const addPartBtn = document.getElementById('addPart');
      function reindexParts(){
        // remove any incomplete rows (safety) then update indexes
        cleanPartsRows();
        Array.from(partsTableBody.querySelectorAll('tr')).forEach((r,i)=>{
          const idx = r.querySelector('.idx'); if(idx) idx.textContent = i+1;
        });
      }

      function createPartRow(data){
        const tr = document.createElement('tr');
        const tdIdx = document.createElement('td'); tdIdx.className = 'idx'; tdIdx.style.textAlign = 'center'; tr.appendChild(tdIdx);

        // code
        const tdCode = document.createElement('td');
        const inCode = document.createElement('input'); inCode.type='text'; inCode.name='parts_code[]'; inCode.value = data && data.code ? data.code : ''; inCode.placeholder='รหัสอะไหล่';
        const wrapCode = document.createElement('div'); wrapCode.className = 'field';
        const lblCode = document.createElement('span'); lblCode.className = 'label-text'; lblCode.textContent = 'รหัสอะไหล่';
        wrapCode.appendChild(lblCode); wrapCode.appendChild(inCode); tdCode.appendChild(wrapCode); tr.appendChild(tdCode);

        // name
        const tdName = document.createElement('td');
        const inName = document.createElement('input'); inName.type='text'; inName.name='parts_name[]'; inName.value = data && data.name ? data.name : ''; inName.placeholder='ชื่ออะไหล่';
        const wrapName = document.createElement('div'); wrapName.className = 'field';
        const lblName = document.createElement('span'); lblName.className = 'label-text'; lblName.textContent = 'ชื่ออะไหล่';
        wrapName.appendChild(lblName); wrapName.appendChild(inName); tdName.appendChild(wrapName); tr.appendChild(tdName);

        // qty
        const tdQty = document.createElement('td');
        const inQty = document.createElement('input'); inQty.type='number'; inQty.name='parts_qty[]'; inQty.min='0'; inQty.value = data && data.qty ? data.qty : 1;
        const wrapQty = document.createElement('div'); wrapQty.className = 'field';
        const lblQty = document.createElement('span'); lblQty.className = 'label-text'; lblQty.textContent = 'จำนวน';
        wrapQty.appendChild(lblQty); wrapQty.appendChild(inQty); tdQty.appendChild(wrapQty); tr.appendChild(tdQty);

        // price
        const tdPrice = document.createElement('td');
        const inPrice = document.createElement('input'); inPrice.type='number'; inPrice.name='parts_price[]'; inPrice.step='0.01'; inPrice.min='0'; inPrice.value = data && data.price ? data.price : '';
        const wrapPrice = document.createElement('div'); wrapPrice.className = 'field';
        const lblPrice = document.createElement('span'); lblPrice.className = 'label-text'; lblPrice.textContent = 'ราคา';
        wrapPrice.appendChild(lblPrice); wrapPrice.appendChild(inPrice); tdPrice.appendChild(wrapPrice); tr.appendChild(tdPrice);

        // note
        const tdNote = document.createElement('td');
        const inNote = document.createElement('input'); inNote.type='text'; inNote.name='parts_note[]'; inNote.value = data && data.note ? data.note : ''; inNote.placeholder='หมายเหตุ';
        const wrapNote = document.createElement('div'); wrapNote.className = 'field';
        const lblNote = document.createElement('span'); lblNote.className = 'label-text'; lblNote.textContent = 'หมายเหตุ';
        wrapNote.appendChild(lblNote); wrapNote.appendChild(inNote); tdNote.appendChild(wrapNote); tr.appendChild(tdNote);

        const tdAct = document.createElement('td'); tdAct.style.textAlign='center';
        const del = document.createElement('button'); del.type='button'; del.className='btn small removePart'; del.innerHTML = '<svg class="icon small" aria-hidden="true"><use xlink:href="#icon-delete"></use></svg>';
        del.addEventListener('click', ()=>{ tr.remove(); reindexParts(); }); tdAct.appendChild(del); tr.appendChild(tdAct);

        partsTableBody.appendChild(tr); reindexParts();
        return tr;
      }

      if(addPartBtn){ addPartBtn.addEventListener('click', ()=>{ createPartRow({}); cleanPartsRows(); }); }

      // Ensure initial rows: clear existing and create three blank rows (make rows 1-2 match template)
      if(partsTableBody){
        partsTableBody.innerHTML = '';
        for(let i=0;i<3;i++) createPartRow({});
        // ensure no stray/incomplete rows are visible
        cleanPartsRows();
      }

      // remove incomplete rows helper: deletes any TRs with fewer than expected cells
      function cleanPartsRows(){
        if(!partsTableBody) return;
        Array.from(partsTableBody.querySelectorAll('tr')).forEach(tr=>{
          if(tr.querySelectorAll('td').length < 7) tr.remove();
        });
      }

      // map fieldId -> Array<File>
      const filesMap = {};

      function renderPreview(fieldId){
        const card = gallery.querySelector(`.upload-card[data-field="${fieldId}"]`);
        if(!card) return;
        const preview = card.querySelector('.preview');
        preview.innerHTML = '';
        const list = filesMap[fieldId] || [];
        // toggle multi / has-preview classes when files present
        if(list.length > 1) card.classList.add('multi'); else card.classList.remove('multi');
        if(list.length > 0) card.classList.add('has-preview'); else card.classList.remove('has-preview');
        // update attach-count badge (modern badge, animated)
        const countEl = card.querySelector('.attach-count');
        if(countEl){
          if(list.length > 0){
            countEl.textContent = '(จำนวน ' + list.length + ' รูป)';
            countEl.setAttribute('data-show','true');
            countEl.setAttribute('aria-hidden','false');
            countEl.setAttribute('title', 'จำนวน ' + list.length + ' รูปที่แนบ');
            countEl.setAttribute('aria-label', 'จำนวน ' + list.length + ' รูปที่แนบ');
          } else {
            countEl.textContent = '';
            countEl.setAttribute('data-show','false');
            countEl.setAttribute('aria-hidden','true');
          }
        }
        list.forEach((file, idx) => {
          if(!file.type.startsWith('image/')) return;
          const wrap = document.createElement('div'); wrap.className = 'thumb';
          const img = document.createElement('img'); img.className = 'thumb-img';
          img.alt = file.name;
          const reader = new FileReader();
          reader.onload = e => { img.src = e.target.result; };
          reader.readAsDataURL(file);
          wrap.appendChild(img);
          const del = document.createElement('button'); del.className = 'remove-btn'; del.title = 'ลบรูป'; del.innerHTML = '<svg class="icon" aria-hidden="true"><use xlink:href="#icon-delete"></use></svg>';
          del.addEventListener('click', ev=>{ ev.stopPropagation(); removeFile(fieldId, idx); });
          wrap.appendChild(del);
          img.addEventListener('click', (ev)=>{ ev.stopPropagation(); openLightbox(fieldId, idx); });
          preview.appendChild(wrap);
        });
        // allow clicking count badge to open viewer
        if(countEl){ countEl.onclick = e=>{ e.stopPropagation(); if((filesMap[fieldId]||[]).length) openLightbox(fieldId, 0); }; }
      }

      function removeFile(fieldId, index){
        if(!filesMap[fieldId]) return;
        filesMap[fieldId].splice(index,1);
        renderPreview(fieldId);
      }

      gallery.querySelectorAll('.upload-card').forEach(card => {
        const field = card.dataset.field;
        const input = card.querySelector('input[type=file]');
        const preview = card.querySelector('.preview');
        const multiple = input.hasAttribute('multiple');
        filesMap[field] = filesMap[field] || [];

        card.addEventListener('click', e => { if(e.target.tagName === 'INPUT' || e.target.classList.contains('remove-btn')) return; if(e.target.closest('.preview')) return; input.click(); });

        input.addEventListener('change', ()=>{
          const chosen = Array.from(input.files || []);
          if(multiple) filesMap[field] = filesMap[field].concat(chosen);
          else filesMap[field] = chosen.slice(0,1);
          renderPreview(field);
        });

        // drag events
        ['dragenter','dragover'].forEach(ev=>{ card.addEventListener(ev, e=>{ e.preventDefault(); card.classList.add('dragover'); }); });
        ['dragleave','drop'].forEach(ev=>{ card.addEventListener(ev, e=>{ e.preventDefault(); card.classList.remove('dragover'); }); });
        card.addEventListener('drop', e=>{
          const dt = e.dataTransfer; if(!dt) return;
          const dropped = Array.from(dt.files || []);
          if(dropped.length){
            if(multiple) filesMap[field] = filesMap[field].concat(dropped);
            else filesMap[field] = [dropped[0]];
            renderPreview(field);
          }
        });
      });

      // attach all opens first input
      const attachAll = document.getElementById('attachAllBtn');
      if(attachAll){ attachAll.addEventListener('click', ()=>{ const first = gallery.querySelector('input[type=file]'); if(first) first.click(); }); }

      // lightbox viewer with navigation
      document.body.insertAdjacentHTML('beforeend', '\n        <div id="lightbox" class="lightbox" aria-hidden="true">\n          <div class="imgwrap">\n            <button class="close" aria-label="ปิด">✕</button>\n            <button class="nav prev" aria-label="ก่อนหน้า">‹</button>\n            <div class="imgframe"><img src="" alt="preview"><div class="counter" aria-hidden="true"></div></div>\n            <button class="nav next" aria-label="ถัดไป">›</button>\n          </div>\n        </div>\n      ');
      const lb = document.getElementById('lightbox');
      let lbState = { fieldId: null, index: 0 };
      function openLightbox(fieldId, index){
        const list = filesMap[fieldId] || [];
        if(!list || !list.length) return;
        lbState.fieldId = fieldId; lbState.index = index || 0;
        const file = list[lbState.index];
        const img = lb.querySelector('.imgframe img');
        const counter = lb.querySelector('.counter');
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; counter.textContent = (lbState.index+1) + ' / ' + list.length; };
        reader.readAsDataURL(file);
        lb.classList.add('open'); lb.setAttribute('aria-hidden','false');
      }
      function closeLightbox(){ lb.classList.remove('open'); lb.setAttribute('aria-hidden','true'); lbState = { fieldId:null, index:0 }; }
      function lbNext(){ if(!lbState.fieldId) return; const list = filesMap[lbState.fieldId]||[]; lbState.index = (lbState.index+1)%list.length; openLightbox(lbState.fieldId, lbState.index); }
      function lbPrev(){ if(!lbState.fieldId) return; const list = filesMap[lbState.fieldId]||[]; lbState.index = (lbState.index-1+list.length)%list.length; openLightbox(lbState.fieldId, lbState.index); }
      lb.addEventListener('click', e=>{ if(e.target.id==='lightbox' || e.target.classList.contains('close')) closeLightbox(); });
      lb.querySelector('.nav.next').addEventListener('click', e=>{ e.stopPropagation(); lbNext(); });
      lb.querySelector('.nav.prev').addEventListener('click', e=>{ e.stopPropagation(); lbPrev(); });
      document.addEventListener('keydown', e=>{ if(!lb.classList.contains('open')) return; if(e.key==='Escape') closeLightbox(); if(e.key==='ArrowRight') lbNext(); if(e.key==='ArrowLeft') lbPrev(); });

      // intercept submit to send filesMap via FormData
      form.addEventListener('submit', function(e){
        e.preventDefault();
        resultBox.textContent = 'กำลังบันทึก...';
        const fd = new FormData();
        // append non-file fields
        Array.from(form.elements).forEach(el=>{
          if(!el.name) return;
          if(el.type==='file') return;
          if(el.type==='checkbox') { if(el.checked) fd.append(el.name, el.value || 'on'); return; }
          if(el.type==='radio') { if(el.checked) fd.append(el.name, el.value); return; }
          if(el.tagName==='SELECT' && el.multiple){ Array.from(el.selectedOptions).forEach(opt=> fd.append(el.name, opt.value)); return; }
          fd.append(el.name, el.value || '');
        });

        // append files from filesMap
        Object.keys(filesMap).forEach(fieldId=>{
          const cardInput = gallery.querySelector(`.upload-card[data-field="${fieldId}"] input[type=file]`);
          const fieldName = cardInput ? cardInput.name : fieldId;
          (filesMap[fieldId] || []).forEach(f => fd.append(fieldName, f));
        });

        fetch(form.action || window.location.href, {method:'POST', body:fd}).then(r=>r.text()).then(txt=>{
          resultBox.innerHTML = txt;
        }).catch(err=>{ resultBox.textContent = 'เกิดข้อผิดพลาด: '+ err.message });
      });

    })();
  </script>
</body>
</html>
