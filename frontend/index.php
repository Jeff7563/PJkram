<?php
require_once __DIR__ . '/../backend/auth.php';
requireLogin();
require_once __DIR__ . '/../backend/index_handler.php';
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>ฟอร์มส่งเคลม</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-dxz0OFf2LjA5efXKwBlenuMxS9IIrLs+1E1iY1p6RhJHciAPxsBHo/djC6AmlL0I" crossorigin="anonymous">
  <link rel="stylesheet" href="../shared/assets/css/theme.css">
  <link rel="stylesheet" href="../shared/assets/css/styles.css"> 
  <link rel="stylesheet" href="../shared/assets/css/styles-claim_form.css"> 
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    :root{--orange:#e65100;--orange-600:#ff7a1a;--black:#121212;--muted:#6b6b6b;--bg:#f7f8fb;--surface:#ffffff;--soft-shadow:rgba(16,24,40,0.06);--icon-card-grad:linear-gradient(135deg,#ff7a1a,#ff8a3d)}
    *{box-sizing:border-box}
    body{font-family:'Kanit',Inter,Segoe UI,Roboto,Arial,sans-serif;margin:0;background:linear-gradient(180deg,#fbfbfd,#ffffff);color:var(--black);-webkit-font-smoothing:antialiased;font-size:16px;line-height:1.55;display:flex;min-height:100vh}
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

    /* Bootstrap layout helpers */
    .field, .inline-field, .claim-row, .upload-card, .upload-card .drop-area {min-width:0}
    .field{display:flex;flex-direction:column;gap:6px}
    /* inline-field: label and input on same row for compact fields (e.g., VIN, owner) */
    .inline-field{flex-direction:row;align-items:center;gap:6px}
    /* force inline layout when needed */
    .inline-field.keep-inline{flex-direction:row !important}
    .inline-field.keep-inline .label-text{min-width:160px}
    .inline-field .label-text{margin-bottom:0;min-width:120px;white-space:nowrap;color:var(--muted);font-weight:700}
    @media (max-width: 768px) {
      .inline-field.keep-inline, .inline-field {flex-direction:column !important;align-items:stretch !important;}
      .inline-field .label-text, .inline-field.keep-inline .label-text {min-width:auto !important;white-space:normal !important;margin-bottom:6px;}
      .form-grid > * {width:100% !important;}
    }
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
    /* Responsive form helpers */
    @media (max-width:768px) {
      .inline-field {
        flex-direction: column !important;
        align-items: stretch !important;
      }
      .inline-field .label-text {
        min-width: auto;
        margin-bottom: 6px;
      }
      .inline-field.keep-inline {
        flex-direction: column !important;
        align-items: stretch !important;
      }
      .inline-field.keep-inline .label-text {
        min-width: auto !important;
        margin-bottom: 6px;
      }
    }
    /* Container responsive */
    @media (max-width:1024px) {
      .container { max-width:100%; margin:20px auto; }
    }
    @media (max-width:768px) {
      .container { max-width:100%; margin:16px auto; padding:0 12px; }
      .main-content { padding:16px !important; }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../shared/assets/includes/sidebar.php'; ?>
  <div class="main-content">
  <main class="container">
    <?php if (!empty($message)): ?>
      <div class="card" style="margin-bottom:12px;padding:10px;background:linear-gradient(90deg,#ff6a00,#ff8f3d);color:#fff;border-radius:8px"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

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

    <form id="claimForm" class="card p-4" method="post" action="../backend/index_handler.php" enctype="multipart/form-data" novalidate>
      <div class="container-fluid">
        <div class="tab-content" id="claimFormTabContent">
          <div class="tab-pane fade show active" id="tab-basic" role="tabpanel" aria-labelledby="tab-basic-tab">
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <div class="form-row-item">
                  <label for="branch" class="form-label">สาขา</label>
                  <select id="branch" name="branch" class="form-select" required>
                    <option value="">-- เลือกสาขา --</option>
                    <option>สาขา สกลนคร</option>
                  </select>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="form-row-item">
                  <label for="claimDate" class="form-label">วันที่ส่งเคลม</label>
                  <input type="date" id="claimDate" name="claimDate" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly required>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="form-row-item align-items-start">
                  <label class="form-label">ประเภทรถ</label>
                  <div class="d-flex gap-2">
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="carType" id="carTypeNew" value="new" checked required>
                      <label class="form-check-label" for="carTypeNew">รถใหม่</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="carType" id="carTypeUsed" value="used" required>
                      <label class="form-check-label" for="carTypeUsed">รถมือสอง</label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="form-row-item">
                  <label for="carBrand" class="form-label">ยี่ห้อ</label>
                  <select id="carBrand" name="carBrand" class="form-select" required>
                    <option value="">-- เลือกยี่ห้อ --</option>
                    <option>Honda</option>
                    <option>Yamaha</option>
                    <option>Vespa</option>
                  </select>
                </div>
              </div>

              <div class="col-12 col-md-6" id="gradeLabel">
                <div class="form-row-item">
                  <label for="usedGrade" class="form-label">เกรด</label>
                  <select id="usedGrade" name="usedGrade" class="form-select">
                    <option value="">-- เลือกเกรด --</option>
                    <option value="A_premium">A พรีเมี่ยม</option>
                    <option value="A_w6">A รับประกันเครื่องยนต์ 6 เดือน</option>
                    <option value="C_w1">C รับประกันเครื่องยนต์ 1 เดือน</option>
                    <option value="C_as_is">C ตามสภาพไม่รับประกัน</option>
                  </select>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="row g-2">
                  <div class="col-8">
                    <div class="form-row-item">
                      <label for="vin" class="form-label">หมายเลขตัวถัง <span class="text-danger">*</span></label>
                      <input type="text" id="vin" name="vin" class="form-control" placeholder="VIN Number" required>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="form-row-item">
                      <label for="mileage" class="form-label">เลขไมล์รถ</label>
                      <input type="number" id="mileage" name="mileage" class="form-control" placeholder="0" min="0">
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="row g-2">
                  <div class="col-7">
                    <div class="form-row-item">
                      <label for="sale_date" class="form-label">วันที่ขายรถ</label>
                      <input type="date" id="sale_date" name="sale_date" class="form-control">
                    </div>
                  </div>
                  <div class="col-5">
                    <div class="form-row-item">
                      <label class="form-label">อายุการใช้งาน</label>
                      <input type="text" id="vehicle_age_display" class="form-control" placeholder="-- ปี -- เดือน -- วัน 0 ชั่วโมง" readonly style="background-color: #ffffff; color: #fd0000; font-weight: 600;">
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="form-row-item">
                  <label for="ownerName" class="form-label">ชื่อ-นามสกุล (ผู้ซื้อ) <span class="text-danger">*</span></label>
                  <input type="text" id="ownerName" name="ownerName" class="form-control" placeholder="ชื่อ นามสกุล" required>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="form-row-item">
                  <label for="ownerPhone" class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                  <input type="text" id="ownerPhone" name="ownerPhone" class="form-control" placeholder="เบอร์โทรศัพท์" required>
                </div>
              </div>
            </div>

            <div class="row g-3 mt-2">
              <div class="col-12">
                <h5 class="fw-bold mb-3" style="color: #222; font-size: 1rem;">รายละเอียดปัญหาที่ลูกค้าแจ้ง :</h5>
                <div class="mb-3">
                  <textarea id="problemDesc" name="problemDesc" rows="4" class="form-control" placeholder="อธิบายปัญหาที่ลูกค้าแจ้ง" required></textarea>
                </div>
              </div>
            </div>

            <div class="row g-3">
              <div class="col-12">
                <h5 class="fw-bold mb-3" style="color: #222; font-size: 1rem;">ผลการตรวจเช็คปัญหา :</h5>
              </div>
              <div class="col-12 col-md-6">
                <div class="mb-3">
                  <label for="inspectMethod" class="form-label fw-semibold">วิธีตรวจเช็ค</label>
                  <textarea id="inspectMethod" name="inspectMethod" rows="3" class="form-control" placeholder="วิธีตรวจเช็ค" required></textarea>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="mb-3">
                  <label for="inspectCause" class="form-label fw-semibold">สาเหตุของปัญหา</label>
                  <textarea id="inspectCause" name="inspectCause" rows="3" class="form-control" placeholder="สาเหตุของปัญหา" required></textarea>
                </div>
              </div>
              <div class="col-12">
                <div class="alert alert-info mb-0" style="background-color: #fff6f6; border-left: 4px solid var(--primary-orange); font-size: 0.95rem;">
                  <strong>***หมายเหตุ :</strong>
                  <ol style="margin: 6px 0 0 18px; padding: 0;">
                    <li>รถมือสองมีปัญหาปรึกษาช่างมือสอง เบอร์โทรศัพท์ พี่สีเมือง 061-0190011 พี่บัว 093-3197117 หรือ 042-71135 ต่อ 201</li>
                    <li>รถใหม่มีปัญหาปรึกษาศูนย์บริการ Honda 086-4594656 Yamaha 086-4550614 Vespa 099-1285556</li>
                  </ol>
                </div>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="tab-images" role="tabpanel" aria-labelledby="tab-images-tab">
            <fieldset class="full images">
              <legend>แนบรูปภาพปัญหา :</legend>
              <div class="image-uploader">
                <div class="image-gallery" id="imageGallery">
                  <div class="upload-card" data-field="imgFullCar">
                    <div class="drop-area">
                      <div class="upload-placeholder"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-modern"></use></svg>ภาพรถทั้งคัน</div>
                      <div class="upload-hint">รองรับหลายรูป</div>
                      <span class="attach-count" aria-hidden="true">0</span>
                    </div>
                    <input type="file" id="imgFullCar" name="imgFullCar[]" accept="image/*" multiple>
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
                      <div class="upload-hint">รองรับหลายรูป</div>
                      <span class="attach-count" aria-hidden="true">0</span>
                    </div>
                    <input type="file" id="imgWarranty" name="imgWarranty[]" accept="image/*" multiple>
                    <div class="preview" data-target="imgWarranty"></div>
                  </div>

                  <div class="upload-card" data-field="imgOdometer">
                    <div class="drop-area">
                      <div class="upload-placeholder"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-modern"></use></svg>ภาพเลขไมล์</div>
                      <div class="upload-hint">รองรับหลายรูป</div>
                      <span class="attach-count" aria-hidden="true">0</span>
                    </div>
                    <input type="file" id="imgOdometer" name="imgOdometer[]" accept="image/*" multiple>
                    <div class="preview" data-target="imgOdometer"></div>
                  </div>

                  <div class="upload-card" data-field="imgEstimate">
                    <div class="drop-area">
                      <div class="upload-placeholder"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-modern"></use></svg>ภาพใบประเมินรายการอะไหล่</div>
                      <div class="upload-hint">รองรับหลายรูป</div>
                      <span class="attach-count" aria-hidden="true">0</span>
                    </div>
                    <input type="file" id="imgEstimate" name="imgEstimate[]" accept="image/*" multiple>
                    <div class="preview" data-target="imgEstimate"></div>
                  </div>
                </div>
              </div>
            </fieldset>
          </div>

          <div class="tab-pane fade" id="tab-claim" role="tabpanel" aria-labelledby="tab-claim-tab">
            <div class="row g-3 mt-2">
              <div class="col-12">
                <h5 class="fw-bold mb-3" style="color: #222; font-size: 1rem;">ประเภทการเคลม :</h5>
              </div>
              <div class="col-12 col-md-6">
                <div class="mb-3">
                  <label for="claimCategory" class="form-label fw-semibold">เลือกประเภท</label>
                  <select id="claimCategory" name="claimCategory" class="form-select">
                    <option value="">-- เลือกประเภทการเคลม --</option>
                    <option value="เคลมรถก่อนขาย">เคลมรถก่อนขาย</option>
                    <option value="เคลมปัญหาทางเทคนิค">เคลมปัญหาทางเทคนิค</option>
                    <option value="เคลมรถลูกค้า">เคลมรถลูกค้า</option>
                  </select>
                </div>
              </div>
              
              <div class="col-12">
                <div class="mb-3">
                  <label class="form-label fw-semibold">การดำเนินการ</label>
                  <div class="d-flex flex-wrap gap-2">
                    <?php if (hasTag('repairBranch')): ?>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="claimAction" id="claim_repair" value="repairBranch">
                      <label class="form-check-label" for="claim_repair">ซ่อมที่สาขา</label>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (hasTag('sendHQ')): ?>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="claimAction" id="claim_send" value="sendHQ">
                      <label class="form-check-label" for="claim_send">ส่งซ่อมที่สนญ.</label>
                    </div>
                    <?php endif; ?>

                    <?php if (hasTag('replaceVehicle')): ?>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="claimAction" id="claim_replace" value="replaceVehicle">
                      <label class="form-check-label" for="claim_replace">เปลี่ยนคัน</label>
                    </div>
                    <?php endif; ?>

                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="claimAction" id="claim_other" value="other">
                      <label class="form-check-label" for="claim_other">อื่นๆ</label>
                    </div>
                  </div>
                  <input type="text" id="claimOtherText" name="claimOtherText" class="form-control mt-2 d-none" placeholder="ระบุอื่นๆ">
                </div>
              </div>
            </div>

            

            <section id="partsSection" class="d-none mt-4">
              <div class="row g-3">
                <div class="col-12">
                  <h5 class="fw-bold" style="color: #222; font-size: 1rem;">ระบุรายการอะไหล่ ที่ต้องการเคลม/จำนวน</h5>
                </div>
                <div class="col-12">
                  <div class="table-responsive">
                    <table id="partsTable" class="table table-hover">
                      <thead class="table-light">
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
                  <div class="d-flex justify-content-between align-items-center mt-3" style="width: 100%;">
                    <div></div> <!-- spacer -->
                    <button type="button" id="addPart" class="btn-parts-add px-4" style="border-radius:20px; font-weight: 600;">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                      เพิ่มรายการ
                    </button>
                    <button type="button" id="btnUploadParts" class="btn-parts-upload px-3" style="border-radius:20px;">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                      อัปโหลดรูปภาพ
                    </button>
                    <input type="file" id="imgPartsUpload" name="imgParts[]" accept="image/*" multiple style="position:absolute;width:1px;height:1px;opacity:0;pointer-events:none;">
                  </div>
                  <div id="partsImgPreview" class="parts-img-preview" style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 15px;"></div>
                </div>

                <!-- Approver Section (Visible for RepairBranch / SendHQ) -->
                <div id="approverSection" class="card d-none mt-3 p-3" style="background: #fffdf9; border: 1px dashed var(--primary-orange);">
                  <h4 class="fw-bold mb-3"><svg class="icon small" aria-hidden="true"><use xlink:href="#icon-claim"></use></svg> ผู้อนุมัติการดำเนินการ</h4>
                  <div class="row g-2">
                    <div class="col-12 col-md-4">
                      <label class="form-label fs-sm fw-600">รหัสพนักงาน</label>
                      <select name="approver_id" class="form-select employee-select" data-target-name="approver_name" data-target-sig="approver_signature">
                        <option value="">-- เลือกพนักงาน --</option>
                      </select>
                    </div>
                    <div class="col-12 col-md-4">
                      <label class="form-label fs-sm fw-600">ชื่อผู้อนุมัติ</label>
                      <input type="text" name="approver_name" class="form-control bg-light" readonly placeholder="ชื่อพนักงาน">
                    </div>
                    <div class="col-12 col-md-4">
                      <label class="form-label fs-sm fw-600">ลายเซ็นต์</label>
                      <input type="text" name="approver_signature" class="form-control bg-light" readonly placeholder="ลายเซ็นต์">
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <div id="partsDeliverySection" class="d-none mt-4">
              <div class="row g-3">
                <div class="col-12">
                  <h5 class="fw-bold" style="color: #222; font-size: 1rem;">ประเภทการส่ง อะไหล่</h5>
                </div>
                <div class="col-12">
                  <div class="d-flex flex-wrap gap-3">
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="partsDelivery" id="partsDelivery_stock" value="in_stock" checked>
                      <label class="form-check-label" for="partsDelivery_stock">ซ่อมที่สาขา</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="partsDelivery" id="partsDelivery_hq" value="wait_hq">
                      <label class="form-check-label" for="partsDelivery_hq">รอส่งอะไหล่ จากสนญ.</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="partsDelivery" id="partsDelivery_buy" value="buy_outside">
                      <label class="form-check-label" for="partsDelivery_buy">ซื้ออะไหล่ร้านนอก</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="partsDelivery" id="partsDeliveryOtherRadio" value="other">
                      <label class="form-check-label" for="partsDeliveryOtherRadio">อื่นๆ</label>
                    </div>
                  </div>
                  <input type="text" id="partsDeliveryOtherText" name="partsDeliveryOtherText" class="form-control mt-2 d-none" placeholder="ระบุอื่นๆ">
                </div>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="tab-replace" role="tabpanel" aria-labelledby="tab-replace-tab">
            <div id="replaceBlock" class="card d-none mt-4 p-4">
              <h5 class="fw-bold mb-4" style="color: #222; font-size: 1rem;">รายละเอียดการเปลี่ยนคันใหม่</h5>
              
              <div class="row g-3 mb-3">
                <div class="col-12">
                  <label class="form-label fw-semibold">รถคันเก่า : คงเหลือเงินดาวน์</label>
                </div>
                <div class="col-12 col-md-6">
                  <div class="input-group">
                    <input type="number" class="form-control" name="old_down_balance" placeholder="0.00" step="0.01" min="0">
                    <span class="input-group-text">บาท</span>
                  </div>
                </div>
              </div>

              <h6 class="fw-bold mb-3 mt-4" style="color: #333; font-size: 0.95rem;">รายละเอียดรถคันใหม่</h6>

              <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                  <div class="d-flex gap-3 align-items-center">
                    <div style="flex: 1; min-width: 0;">
                        <label class="form-label fw-semibold">รถคันใหม่ : คงเหลือเงินดาวน์</label>
                        <div class="input-group">
                          <input type="number" class="form-control" name="new_down_balance" placeholder="0.00" step="0.01" min="0">
                          <span class="input-group-text">บาท</span>
                        </div>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                      <label class="form-label fw-semibold">ประเภทรถ</label>
                      <div class="d-flex gap-3 align-items-center" style="height: 30px;">
                        <div class="form-check m-0">
                          <input class="form-check-input" type="radio" name="replaceType" id="replaceType_new" value="รถใหม่">
                          <label class="form-check-label" for="replaceType_new">รถใหม่</label>
                        </div>
                        <div class="form-check m-0">
                          <input class="form-check-input" type="radio" name="replaceType" id="replaceType_used" value="รถมือสอง">
                          <label class="form-check-label" for="replaceType_used">รถมือสอง</label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                  <div class="d-flex gap-10" style="gap: 10px;">
                    <div style="flex: 1; min-width: 0;">
                      <label for="replaceModel" class="form-label fw-semibold">รุ่น</label>
                      <input type="text" id="replaceModel" name="replace_model" class="form-control" placeholder="รุ่น">
                    </div>
                    <div style="flex: 1; min-width: 0;">
                      <label for="replaceColor" class="form-label fw-semibold">สี</label>
                      <input type="text" id="replaceColor" name="replace_color" class="form-control" placeholder="สี">
                    </div>
                    <div style="flex: 1; min-width: 0;">
                      <label for="replaceVin" class="form-label fw-semibold">เลขตัวถัง</label>
                      <input type="text" id="replaceVin" name="replace_vin" class="form-control" placeholder="เลขตัวถัง / VIN">
                    </div>
                  </div>
                </div>
                
                <div class="col-12 col-md-6">
                  <label for="replaceReceiveDate" class="form-label fw-semibold">วันที่รับรถ</label>
                  <input type="date" id="replaceReceiveDate" name="replace_receive_date" class="form-control">
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-12">
                  <label for="replaceReason" class="form-label fw-semibold">สาเหตุที่เปลี่ยนคัน</label>
                    <input type="text" id="replaceReason" name="replace_reason" class="form-control" placeholder="ระบุสาเหตุการเปลี่ยนคัน">
                </div>
              </div>


                <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <div class="row g-2">
                      <div class="col-4">
                        <label for="replace_id" class="form-label fw-semibold">รหัสพนักงาน</label>
                        <select id="replace_id" name="replace_id" class="form-select employee-select" data-target-name="replace_name" data-target-sig="replace_signature">
                           <option value="">-- เลือก --</option>
                        </select>
                      </div>
                      <div class="col-4">
                        <label for="replace_name" class="form-label fw-semibold">ชื่อพนักงาน</label>
                        <input type="text" id="replace_name" name="replace_name" class="form-control bg-light" readonly placeholder="ชื่อพนักงาน">
                      </div>
                      <div class="col-4">
                        <label for="replace_signature" class="form-label fw-semibold">ลายเซ็นต์</label>
                        <input type="text" id="replace_signature" name="replace_signature" class="form-control bg-light" readonly placeholder="ลายเซ็นต์">
                      </div>
                    </div>
                </div>
                
                <div class="col-12 col-md-6">
                    <label for="replace_approve_date" class="form-label fw-semibold">วันที่อนุมัติ</label>
                    <input type="date" id="replace_approve_date" name="replace_approve_date" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
            </div>

              <div class="row mt-3 mb-0">
                <div class="col-12">
                  <p class="text-danger fw-bold" style="font-size:0.95rem; margin-bottom:0;">***หมายเหตุ :</p>
                  <p class="text-danger" style="font-size:0.95rem; margin-bottom:0.25rem;">1. ลูกค้าแจ้งเปลี่ยนคัน ส่งให้สินเชื่อพร้อมใบอนุมัติทุกครั้งที่มีการเปลี่ยน/ตัวจริงแนบมากับสัญญาส่งให้บัญชี</p>
                  <p class="text-danger" style="font-size:0.95rem;">2. สินเชื่อเช็คประกันรถหาย / ทะเบียนแก้ไข พ.ร.บ.-ทะเบียน / บริหารสต็อก ตัดแลกเปลี่ยน / ธุรการสินเชื่อ ตรวจรอบการเปิดขาย กลับมาให้หน่อย</p>
                </div>
              </div>
            </div>
          </div>
        </div> </div> <hr class="my-4" style="border-color: #ececec;">
      <div class="row g-3 mb-3" style="padding: 0 12px;">
        <div class="col-12 col-md-6">
          <div class="mb-3">
            <label for="recorder" class="form-label fw-semibold">ผู้บันทึกส่งเคลม <span class="text-danger">*</span></label>
            <input type="text" id="recorder" name="recorder" class="form-control" placeholder="ชื่อผู้บันทึก" required>
          </div>
        </div>
      </div>

      <div class="row mt-2 mb-3" style="padding: 0 12px;">
        <div class="col-12 d-flex gap-2 justify-content-end">
          <button type="submit" class="btn btn-primary">บันทึกการส่งเคลม</button>
          <button type="reset" class="btn btn-reset">รีเซ็ต</button>
        </div>
      </div>

      <div id="result" class="result mt-3" role="status" aria-live="polite" style="display: none; padding: 0 12px;"></div>
    </form>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-cndY3KSa6nw2pNpGFAvZrKpT8829k3KgAC45Eynl0qsnI9qZC6Qys9VbDomvY1vG" crossorigin="anonymous"></script>
  
  <script>
    (function(){
      // set claimDate value from placeholder if empty so user can edit it
      const _claimDate = document.getElementById('claimDate');
      if(_claimDate && !_claimDate.value){ _claimDate.value = _claimDate.placeholder || '<?php echo date('Y-m-d'); ?>'; }

      // Real-time Age of Vehicle Calculation
      function updateVehicleAge() {
        const saleDateInput = document.getElementById('sale_date');
        const ageDisplay = document.getElementById('vehicle_age_display');
        
        if (!saleDateInput || !ageDisplay) return;
        
        if (!saleDateInput.value) {
          ageDisplay.value = '-- ปี -- เดือน -- วัน 0 ชั่วโมง';
          ageDisplay.style.color = '#666';
          ageDisplay.style.fontWeight = '600';
          return;
        }

        const saleDate = new Date(saleDateInput.value);
        const now = new Date();

        if (now < saleDate) {
          ageDisplay.value = 'วันที่ขายต้องไม่เกินวันปัจจุบัน';
          ageDisplay.style.color = '#e74c3c';
          return;
        }

        let years = now.getFullYear() - saleDate.getFullYear();
        let months = now.getMonth() - saleDate.getMonth();
        let days = now.getDate() - saleDate.getDate();
        let hours = now.getHours() - saleDate.getHours();
        let minutes = now.getMinutes() - saleDate.getMinutes();

        if (minutes < 0) {
            minutes += 60;
            hours--;
        }
        if (hours < 0) {
            hours += 24;
            days--;
        }
        if (days < 0) {
            const previousMonth = new Date(now.getFullYear(), now.getMonth(), 0);
            days += previousMonth.getDate();
            months--;
        }
        if (months < 0) {
            months += 12;
            years--;
        }

        ageDisplay.value = `${years} ปี ${months} เดือน ${days} วัน ${hours} ชั่วโมง ${minutes} นาที`;
        ageDisplay.style.color = '#e74c3c'; // สีแดงเด่นชัด (Text สีแดง แบบ Real Time)
        ageDisplay.style.fontWeight = 'bold';
      }

      setInterval(updateVehicleAge, 60000); // 1 minute
      const saleDateInputEvent = document.getElementById('sale_date');
      if(saleDateInputEvent) saleDateInputEvent.addEventListener('change', updateVehicleAge);

      const gallery = document.getElementById('imageGallery');
      const form = document.getElementById('claimForm');
      const resultBox = document.getElementById('result');
      if(!gallery || !form) return;

      // Car Age Calculation
      const saleDateInput = document.getElementById('sale_date');
      const ageDisplay = document.getElementById('vehicle_age_display');
      
      function calculateVehicleAge() {
        if (!saleDateInput.value) {
          ageDisplay.value = '';
          return;
        }
        const start = new Date(saleDateInput.value);
        start.setHours(0,0,0,0);
        const end = new Date();
        
        let years = end.getFullYear() - start.getFullYear();
        let months = end.getMonth() - start.getMonth();
        let days = end.getDate() - start.getDate();
        let hours = end.getHours();
        
        if (days < 0) {
          months--;
          const prevMonth = new Date(end.getFullYear(), end.getMonth(), 0);
          days += prevMonth.getDate();
        }
        if (months < 0) {
          years--;
          months += 12;
        }
        
        let res = [];
        if (years > 0) res.push(years + " ปี");
        if (months > 0) res.push(months + " เดือน");
        if (days > 0) res.push(days + " วัน");
        res.push(hours + " ชั่วโมง");
        
        ageDisplay.value = res.join(" ");
      }
      saleDateInput.addEventListener('change', calculateVehicleAge);

      // Employee Dropdowns Logic
      let employeesData = [];
      async function loadEmployees() {
        try {
          const res = await fetch('../backend/api_users.php');
          const json = await res.json();
          if (json.success) {
            employeesData = json.data;
            populateEmployeeDropdowns();
          }
        } catch (e) { console.error('Failed to load employees', e); }
      }

      function populateEmployeeDropdowns() {
        const selects = document.querySelectorAll('.employee-select');
        selects.forEach(sel => {
          employeesData.forEach(emp => {
            const opt = document.createElement('option');
            opt.value = emp.employee_id;
            opt.textContent = `${emp.employee_id} - ${emp.name}`;
            sel.appendChild(opt);
          });

          sel.addEventListener('change', function() {
            const targetName = document.getElementsByName(this.dataset.targetName)[0] || document.getElementById(this.dataset.targetName);
            const targetSig = document.getElementsByName(this.dataset.targetSig)[0] || document.getElementById(this.dataset.targetSig);
            const emp = employeesData.find(e => e.employee_id === this.value);
            
            if (targetName) targetName.value = emp ? emp.name : '';
            if (targetSig) targetSig.value = emp ? (emp.signature || 'No Signature') : '';
          });
        });
      }
      loadEmployees();

      // show grade select only for used cars and keep it inline with brand
      const gradeFields = document.querySelectorAll('.grade-field');
      const usedGradeCol = document.getElementById('gradeLabel');
      function updateGradeVisibility(){
        const checkedType = document.querySelector('input[name="carType"]:checked');
        const used = checkedType && checkedType.value === 'used';
        if(usedGradeCol) usedGradeCol.classList.toggle('d-none', !used);
      }
      // listen for carType changes
      document.querySelectorAll('input[name="carType"]').forEach(r => r.addEventListener('change', updateGradeVisibility));
      updateGradeVisibility();

      // Claim action: single-select radios
      const claimActionRadios = document.querySelectorAll('input[name="claimAction"]');
      const replaceBlock = document.getElementById('replaceBlock');
      const approverSection = document.getElementById('approverSection');
      const partsSection = document.getElementById('partsSection');
      const claimOtherTextInit = document.getElementById('claimOtherText');

      function updatePartsVisibility(){
        if(!partsSection) return;
        const sel = document.querySelector('input[name="claimAction"]:checked');
        const val = sel ? sel.value : '';
        // ซ่อนตารางอะไหล่ถ้าเป็นการเปลี่ยนคัน
        const showParts = val === 'repairBranch' || val === 'sendHQ';
        partsSection.classList.toggle('d-none', !showParts);
        
        const partsDeliverySection = document.getElementById('partsDeliverySection');
        if(partsDeliverySection) partsDeliverySection.classList.toggle('d-none', !showParts);
        
        if(approverSection) approverSection.classList.toggle('d-none', !showParts);
      }

      function updateClaimActionVisibility(){
        const sel = document.querySelector('input[name="claimAction"]:checked');
        const val = sel ? sel.value : '';
        if(replaceBlock) replaceBlock.classList.toggle('d-none', val !== 'replaceVehicle');
        if(claimOtherTextInit) { 
          claimOtherTextInit.classList.toggle('d-none', val !== 'other'); 
          if(val !== 'other') claimOtherTextInit.value = ''; 
        }
      }

      claimActionRadios.forEach(r=> r.addEventListener('change', ()=>{ updatePartsVisibility(); updateClaimActionVisibility(); updateCheckedClasses(); }));
      updatePartsVisibility(); updateClaimActionVisibility();

      // show an 'other' input when partsDelivery = other is selected
      const partsDeliveryOtherText = document.getElementById('partsDeliveryOtherText');
      const partsDeliveryRadios = document.querySelectorAll('input[name="partsDelivery"]');
      if(partsDeliveryRadios && partsDeliveryOtherText){
        function updatePartsDeliveryOther(){
          const sel = document.querySelector('input[name="partsDelivery"]:checked');
          const other = sel && sel.value === 'other';
          partsDeliveryOtherText.classList.toggle('d-none', !other);
          if(!other) partsDeliveryOtherText.value = '';
        }
        partsDeliveryRadios.forEach(r=> r.addEventListener('change', updatePartsDeliveryOther));
        updatePartsDeliveryOther();
      }

      function updateCheckedClasses(){
        Array.from(form.querySelectorAll('input[type="checkbox"], input[type="radio"]')).forEach(inp => {
          const lab = inp.closest('label');
          if(!lab) return;
          if(inp.checked) lab.classList.add('checked'); else lab.classList.remove('checked');
        });
      }
      Array.from(form.querySelectorAll('input[type="checkbox"], input[type="radio"]')).forEach(inp => inp.addEventListener('change', updateCheckedClasses));
      updateCheckedClasses();

      // Parts table dynamic rows
      const partsTableBody = document.querySelector('#partsTable tbody');
      const addPartBtn = document.getElementById('addPart');
      function reindexParts(){
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
        const inType = document.createElement('input'); inType.type = 'hidden'; inType.name = 'parts_type[]'; inType.value = data && data.type ? data.type : 'main';
        wrapCode.appendChild(lblCode); wrapCode.appendChild(inCode); wrapCode.appendChild(inType); tdCode.appendChild(wrapCode); tr.appendChild(tdCode);

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

      if(partsTableBody){
        partsTableBody.innerHTML = '';
        for(let i=0;i<3;i++) createPartRow({});
        cleanPartsRows();
      }

      function cleanPartsRows(){
        if(!partsTableBody) return;
        Array.from(partsTableBody.querySelectorAll('tr')).forEach(tr=>{
          if(tr.querySelectorAll('td').length < 7) tr.remove();
        });
      }

      const filesMap = {};
      const fieldLabel = {
        imgFullCar:   'รถทั้งคัน', imgSpot:       'จุดปัญหา', imgPart:       'ชิ้นส่วน',
        imgWarranty:   'สมุดรับประกัน', imgOdometer:   'เลขไมล์', imgEstimate:   'ใบประเมิน'
      };

      function getVin() {
        const v = (document.getElementById('vin') || {}).value || '';
        return v.trim().replace(/\s+/g, '_') || 'XXXXXXX';
      }

      function renderPreview(fieldId){
        const card = gallery.querySelector(`.upload-card[data-field="${fieldId}"]`);
        if(!card) return;
        const preview = card.querySelector('.preview');
        preview.innerHTML = '';
        const list = filesMap[fieldId] || [];
        if(list.length > 1) card.classList.add('multi'); else card.classList.remove('multi');
        if(list.length > 0) card.classList.add('has-preview'); else card.classList.remove('has-preview');
        
        const countEl = card.querySelector('.attach-count');
        if(countEl){
          if(list.length > 0){
            countEl.textContent = '(จำนวน ' + list.length + ' รูป)';
            countEl.setAttribute('data-show','true');
          } else {
            countEl.textContent = '';
            countEl.setAttribute('data-show','false');
          }
        }
        
        list.forEach((file, idx) => {
          if(!file.type.startsWith('image/')) return;
          const ext = file.name.split('.').pop() || 'jpg';
          const prefix = fieldLabel[fieldId] || fieldId;
          const vin = getVin();
          const fixedName = `${prefix}_${vin}_${idx + 1}.${ext}`;

          const wrap = document.createElement('div'); wrap.className = 'thumb';
          const img = document.createElement('img'); img.className = 'thumb-img';
          img.alt = fixedName;
          const reader = new FileReader();
          reader.onload = e => {
            img.src = e.target.result;
            const dlLink = wrap.querySelector('.dl-link');
            if (dlLink) dlLink.href = e.target.result;
          };
          reader.readAsDataURL(file);
          wrap.appendChild(img);

          const dl = document.createElement('a');
          dl.className = 'dl-link remove-btn';
          dl.title = `ดาวน์โหลด: ${fixedName}`;
          dl.download = fixedName;
          dl.href = '#';
          dl.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>';
          dl.style.cssText = 'position:absolute;top:8px;left:8px;width:28px;height:28px;background:rgba(0,0,0,0.5);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;';
          wrap.appendChild(dl);

          const del = document.createElement('button'); del.className = 'remove-btn'; del.title = 'ลบรูป';
          del.innerHTML = '<svg class="icon" aria-hidden="true"><use xlink:href="#icon-delete"></use></svg>';
          del.addEventListener('click', ev=>{ ev.stopPropagation(); removeFile(fieldId, idx); });
          wrap.appendChild(del);

          img.addEventListener('click', (ev)=>{ ev.stopPropagation(); openLightbox(fieldId, idx); });
          preview.appendChild(wrap);
        });
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
        const multiple = input.hasAttribute('multiple');
        filesMap[field] = filesMap[field] || [];

        card.addEventListener('click', e => { if(e.target.tagName === 'INPUT' || e.target.classList.contains('remove-btn')) return; if(e.target.closest('.preview')) return; input.click(); });

        input.addEventListener('change', ()=>{
          const chosen = Array.from(input.files || []);
          if(multiple) filesMap[field] = filesMap[field].concat(chosen);
          else filesMap[field] = chosen.slice(0,1);
          renderPreview(field);
          input.value = ''; // เคลียร์เพื่อเลือกไฟล์เดิมซ้ำได้
        });

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

      // ระบบอัปโหลดรูปภาพอะไหล่
      const btnUploadParts = document.getElementById('btnUploadParts');
      const imgPartsUpload = document.getElementById('imgPartsUpload');
      const partsPreview = document.getElementById('partsImgPreview');
      const partsFieldId = 'imgParts[]';
      filesMap[partsFieldId] = [];

      if (btnUploadParts && imgPartsUpload) {
        btnUploadParts.addEventListener('click', () => imgPartsUpload.click());
        imgPartsUpload.addEventListener('change', function() {
          const chosenFiles = Array.from(this.files || []);
          if (chosenFiles.length > 0) {
            filesMap[partsFieldId] = filesMap[partsFieldId].concat(chosenFiles);
            renderPartsPreview();
          }
          this.value = ''; 
        });
      }

      function renderPartsPreview() {
        partsPreview.innerHTML = '';
        const list = filesMap[partsFieldId];
        const uploadSvg = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>`;
        
        if (list.length === 0) {
          btnUploadParts.innerHTML = uploadSvg + ' อัปโหลดรูปภาพ';
        } else {
          btnUploadParts.innerHTML = uploadSvg + ` อัปโหลดแล้ว ${list.length} รูป`;
        }

        list.forEach((file, idx) => {
          const wrap = document.createElement('div');
          wrap.style.cssText = 'position: relative; width: 120px; height: 120px; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.05);';
          
          const img = document.createElement('img');
          img.style.cssText = 'width: 100%; height: 100%; object-fit: cover; display: block; cursor: pointer;';
          const reader = new FileReader();
          reader.onload = e => { img.src = e.target.result; };
          reader.readAsDataURL(file);
          
          const del = document.createElement('button');
          del.type = 'button';
          del.innerHTML = '×';
          del.style.cssText = 'position: absolute; top: 6px; right: 6px; background: rgba(255, 30, 30, 0.85); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px; padding: 0;';
          del.addEventListener('click', (ev) => {
            ev.stopPropagation();
            filesMap[partsFieldId].splice(idx, 1);
            renderPartsPreview();
          });

          img.addEventListener('click', (ev) => { ev.stopPropagation(); openLightbox(partsFieldId, idx); });

          wrap.appendChild(img);
          wrap.appendChild(del);
          partsPreview.appendChild(wrap);
        });
      }

      // ระบบ Lightbox
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

      // ดักการกด Submit 
      form.addEventListener('submit', function(e){
        e.preventDefault();
        
        resultBox.style.display = 'block';
        resultBox.innerHTML = '<div style="padding: 10px; background: #fff3cd; color: #856404; border-radius: 8px; font-weight: bold; margin-bottom: 10px;">⏳ กำลังบันทึกข้อมูลเคลม... กรุณารอสักครู่</div>';

        if (!this.checkValidity()) {
          this.reportValidity();
          return;
        }
        
        // ใช้ FormData กวาดข้อมูลจากหน้าเว็บ "ทุกช่อง" โดยอัตโนมัติ
        const fd = new FormData(this);

        // วนลูปเอารูปภาพจาก 6 กล่องด้านบน ยัดใส่ลงไปใน FormData
        gallery.querySelectorAll('.upload-card').forEach(card => {
            const fieldId = card.dataset.field;
            const inputEl = card.querySelector('input[type="file"]');
            if(inputEl && filesMap[fieldId]) {
                const inputName = inputEl.getAttribute('name');
                filesMap[fieldId].forEach(file => {
                    fd.append(inputName, file);
                });
            }
        });

        // เอารูปภาพจากตารางอะไหล่ ยัดใส่ลงไปใน FormData ด้วย (ถ้ามี)
        const partsFieldId = 'imgParts[]';
        if(filesMap[partsFieldId]) {
            filesMap[partsFieldId].forEach(file => {
                fd.append(partsFieldId, file);
            });
        }

        // ยิงข้อมูลไปที่ index_handler.php
        fetch(form.action, {
          method: 'POST', 
          body: fd
        })
        .then(r => r.text())
        .then(txt => {
          resultBox.innerHTML = txt; // โชว์ข้อความที่ส่งกลับมาจาก Handler
          
          if(txt.includes('✅')) {
             // ถ้าสำเร็จ ให้เคลียร์รูปภาพในหน้าเว็บเตรียมรอเคสต่อไป
             document.querySelectorAll('.preview').forEach(p => p.innerHTML='');
             const partsPreview = document.getElementById('partsImgPreview');
             if(partsPreview) partsPreview.innerHTML = '';
             
             Object.keys(filesMap).forEach(k => filesMap[k] = []);
             
             const btnUploadParts = document.getElementById('btnUploadParts');
             if(btnUploadParts) btnUploadParts.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg> อัปโหลดรูปภาพ`;

             window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
          }
        })
        .catch(err => { 
          resultBox.innerHTML = '<div style="padding: 10px; background: #f8d7da; color: #721c24; border-radius: 8px; font-weight: bold; margin-bottom: 10px;">❌ เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: '+ err.message + '</div>'; 
        });
      });

    })();
  </script>
</body>
</html>