<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ประวัติเคลม - ระบบจัดการฟอร์มส่งเคลม</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/styles-history.css">
</head>
<body>

  <?php include 'includes/sidebar.php'; ?>

  <div class="main-content">
    <div class="container-fluid p-0">
      
      <!-- Filter Bar -->
      <div class="filter-bar mb-4">
        <div class="row w-100 g-3 align-items-center">
          <div class="col-12 col-lg-auto flex-grow-1">
            <div class="d-flex flex-wrap gap-2">
              <input type="text" placeholder="ค้นหาเอกสาร..." class="form-control" style="width: 250px;">
              <select class="form-select" style="width: auto; min-width: 140px;">
                <option value="">สาขา</option>
                <option value="sakon">สกลนคร</option>
              </select>
              <select class="form-select" style="width: auto; min-width: 140px;">
                <option value="" selected>สถานะ</option>
                <option value="wait">รอสั่งอะไหล่</option>
                <option value="complete">ซ่อมเสร็จสิ้น</option>
              </select>
              <input type="date" class="form-control" style="width: auto;" value="2026-03-24">
            </div>
          </div>
          <div class="col-12 col-lg-auto">
            <div class="d-flex gap-2 justify-content-lg-end">
              <button class="btn-search px-4">ค้นหา</button>
              <button class="btn-reset px-4">รีเซ็ต</button>
            </div>
          </div>
        </div>
      </div>

      <!-- History Grid -->
      <div class="row row-cols-1 row-cols-lg-2 g-4">
        
        <!-- Card 1 -->
        <div class="col">
          <div class="history-card">
            <div class="hc-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
              <div class="d-flex gap-3 align-items-center">
                <div class="hc-date">25/3/2569</div>
                <div class="hc-doc fw-bold">เลขที่เอกสาร : TS01-001</div>
              </div>
              <div class="d-flex gap-2 align-items-center">
                <div class="hc-badge">สถานะ : รอสั่งอะไหล่</div>
                <a href="edit_claim.php?id=TS01-001" class="hc-btn">แก้ไข</a>
              </div>
            </div>
            
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">สาขา :</div>
                  <input type="text" class="hc-input form-control" value="สำนักงานใหญ่" readonly>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">ประเภทการเคลม :</div>
                  <input type="text" class="hc-input form-control" value="รถลูกค้า - เคลมปกติ" readonly>
                </div>
              </div>
              
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">ชื่อผู้ใช้งาน :</div>
                  <input type="text" class="hc-input form-control" value="นาย เทส" readonly>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">เบอร์โทรศัพท์ :</div>
                  <input type="text" class="hc-input form-control" value="08080000" readonly>
                </div>
              </div>
              
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">หมายเลขตัวถัง :</div>
                  <input type="text" class="hc-input form-control" value="ABVAXGASZ1AS" readonly>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">วันที่สั่งคืนอะไหล่ :</div>
                  <input type="text" class="hc-input form-control" value="-" readonly>
                </div>
              </div>
              
              <div class="col-12">
                <div class="hc-field-group">
                  <div class="hc-label">วันที่ส่งเคลม :</div>
                  <input type="text" class="hc-input form-control" value="25/03/2569" readonly>
                </div>
              </div>
            </div>

            <div class="hc-textarea-group mt-3">
              <label class="hc-textarea-label fw-bold mb-1 d-block">รายละเอียดปัญหาที่ลูกค้าแจ้ง :</label>
              <textarea class="hc-textarea form-control" readonly>ลูกค้าแจ้งว่าไฟเลี้ยวไม่ขึ้น</textarea>
            </div>

            <div class="row g-3 mt-1">
              <div class="col-12 col-md-6">
                <div class="hc-textarea-group">
                  <label class="hc-textarea-label fw-bold mb-1 d-block">วิธีการแก้ไข :</label>
                  <textarea class="hc-textarea form-control" readonly>ช่างได้ทำการตรวจสอบและได้เปลี่ยนเซ็นเซอร์เกียร์</textarea>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="hc-textarea-group">
                  <label class="hc-textarea-label fw-bold mb-1 d-block">สาเหตุของปัญหา :</label>
                  <textarea class="hc-textarea form-control" readonly>เซ็นเซอร์เกียร์พัง</textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="col">
          <div class="history-card">
            <div class="hc-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
              <div class="d-flex gap-3 align-items-center">
                <div class="hc-date">25/3/2569</div>
                <div class="hc-doc fw-bold">เลขที่เอกสาร : TS01-002</div>
              </div>
              <div class="d-flex gap-2 align-items-center">
                <div class="hc-badge">สถานะ : รอสั่งอะไหล่</div>
                <a href="edit_claim.php?id=TS01-002" class="hc-btn">แก้ไข</a>
              </div>
            </div>
            
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">สาขา :</div>
                  <input type="text" class="hc-input form-control" value="เชียงใหม่" readonly>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">ประเภทการเคลม :</div>
                  <input type="text" class="hc-input form-control" value="รถลูกค้า - เคลมปกติ" readonly>
                </div>
              </div>
              
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">ชื่อผู้ใช้งาน :</div>
                  <input type="text" class="hc-input form-control" value="นาย เทส2" readonly>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">เบอร์โทรศัพท์ :</div>
                  <input type="text" class="hc-input form-control" value="09090000" readonly>
                </div>
              </div>
              
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">หมายเลขตัวถัง :</div>
                  <input type="text" class="hc-input form-control" value="XYZAXGASZ2XZ" readonly>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">วันที่สั่งคืนอะไหล่ :</div>
                  <input type="text" class="hc-input form-control" value="-" readonly>
                </div>
              </div>
              
              <div class="col-12">
                <div class="hc-field-group">
                  <div class="hc-label">วันที่ส่งเคลม :</div>
                  <input type="text" class="hc-input form-control" value="25/03/2569" readonly>
                </div>
              </div>
            </div>

            <div class="hc-textarea-group mt-3">
              <label class="hc-textarea-label fw-bold mb-1 d-block">รายละเอียดปัญหาที่ลูกค้าแจ้ง :</label>
              <textarea class="hc-textarea form-control" readonly>สตาร์ทไม่ติด แบตเสื่อม</textarea>
            </div>

            <div class="row g-3 mt-1">
              <div class="col-12 col-md-6">
                <div class="hc-textarea-group">
                  <label class="hc-textarea-label fw-bold mb-1 d-block">วิธีการแก้ไข :</label>
                  <textarea class="hc-textarea form-control" readonly>เปลี่ยนแบตเตอรี่ใหม่</textarea>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="hc-textarea-group">
                  <label class="hc-textarea-label fw-bold mb-1 d-block">สาเหตุของปัญหา :</label>
                  <textarea class="hc-textarea form-control" readonly>แบตเสื่อมตามอายุ</textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="col">
          <div class="history-card">
            <div class="hc-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
              <div class="d-flex gap-3 align-items-center">
                <div class="hc-date">25/3/2569</div>
                <div class="hc-doc fw-bold">เลขที่เอกสาร : TS01-003</div>
              </div>
              <div class="d-flex gap-2 align-items-center">
                <div class="hc-badge">สถานะ : รอส่งอะไหล่</div>
                <a href="edit_claim.php" class="hc-btn">แก้ไข</a>
              </div>
            </div>
            
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">สาขา :</div>
                  <input type="text" class="hc-input form-control" value="ภูเก็ต" readonly>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">ประเภทการเคลม :</div>
                  <input type="text" class="hc-input form-control" value="รถลูกค้า - เคลมปกติ" readonly>
                </div>
              </div>
              
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">ชื่อผู้ใช้งาน :</div>
                  <input type="text" class="hc-input form-control" value="นาย ใจดี" readonly>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">เบอร์โทรศัพท์ :</div>
                  <input type="text" class="hc-input form-control" value="08111111" readonly>
                </div>
              </div>
              
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">หมายเลขตัวถัง :</div>
                  <input type="text" class="hc-input form-control" value="PKXAXGASZ2XZ" readonly>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">วันที่สั่งคืนอะไหล่ :</div>
                  <input type="text" class="hc-input form-control" value="-" readonly>
                </div>
              </div>
              
              <div class="col-12">
                <div class="hc-field-group">
                  <div class="hc-label">วันที่ส่งเคลม :</div>
                  <input type="text" class="hc-input form-control" value="25/03/2569" readonly>
                </div>
              </div>
            </div>

            <div class="hc-textarea-group mt-3">
              <label class="hc-textarea-label fw-bold mb-1 d-block">รายละเอียดปัญหาที่ลูกค้าแจ้ง :</label>
              <textarea class="hc-textarea form-control" readonly>เบรกไม่อยู่ มีเสียงดัง</textarea>
            </div>

            <div class="row g-3 mt-1">
              <div class="col-12 col-md-6">
                <div class="hc-textarea-group">
                  <label class="hc-textarea-label fw-bold mb-1 d-block">วิธีการแก้ไข :</label>
                  <textarea class="hc-textarea form-control" readonly>เปลี่ยนผ้าเบรกใหม่ ล้างทำความสะอาดลูกสูบ</textarea>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="hc-textarea-group">
                  <label class="hc-textarea-label fw-bold mb-1 d-block">สาเหตุของปัญหา :</label>
                  <textarea class="hc-textarea form-control" readonly>ผ้าเบรกหมด ฝุ่นจับเยอะ</textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Card 4 -->
        <div class="col">
          <div class="history-card">
            <div class="hc-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
              <div class="d-flex gap-3 align-items-center">
                <div class="hc-date">25/3/2569</div>
                <div class="hc-doc fw-bold">เลขที่เอกสาร : TS01-004</div>
              </div>
              <div class="d-flex gap-2 align-items-center">
                <div class="hc-badge bg-primary-orange text-white">สถานะ : ซ่อมเสร็จสิ้น</div>
                <a href="edit_claim.php?id=TS01-004" class="hc-btn">แก้ไข</a>
              </div>
            </div>
            
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">สาขา :</div>
                  <input type="text" class="hc-input form-control" value="โคราช" readonly>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">ประเภทการเคลม :</div>
                  <input type="text" class="hc-input form-control" value="รถลูกค้า - เคลมพิเศษ" readonly>
                </div>
              </div>
              
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">ชื่อผู้ใช้งาน :</div>
                  <input type="text" class="hc-input form-control" value="สมชาย หายห่วง" readonly>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">เบอร์โทรศัพท์ :</div>
                  <input type="text" class="hc-input form-control" value="08999999" readonly>
                </div>
              </div>
              
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">หมายเลขตัวถัง :</div>
                  <input type="text" class="hc-input form-control" value="NKRAXGASZ2XZ" readonly>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="hc-field-group">
                  <div class="hc-label">วันที่สั่งคืนอะไหล่ :</div>
                  <input type="text" class="hc-input form-control" value="26/03/2569" readonly>
                </div>
              </div>
              
              <div class="col-12">
                <div class="hc-field-group">
                  <div class="hc-label">วันที่ส่งเคลม :</div>
                  <input type="text" class="hc-input form-control" value="24/03/2569" readonly>
                </div>
              </div>
            </div>

            <div class="hc-textarea-group mt-3">
              <label class="hc-textarea-label fw-bold mb-1 d-block">รายละเอียดปัญหาที่ลูกค้าแจ้ง :</label>
              <textarea class="hc-textarea form-control" readonly>โช๊คหน้าน้ำมันซึม</textarea>
            </div>

            <div class="row g-3 mt-1">
              <div class="col-12 col-md-6">
                <div class="hc-textarea-group">
                  <label class="hc-textarea-label fw-bold mb-1 d-block">วิธีการแก้ไข :</label>
                  <textarea class="hc-textarea form-control" readonly>เปลี่ยนซีลโช๊คและเติมน้ำมันโช๊คใหม่</textarea>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="hc-textarea-group">
                  <label class="hc-textarea-label fw-bold mb-1 d-block">สาเหตุของปัญหา :</label>
                  <textarea class="hc-textarea form-control" readonly>ซีลเสื่อมสภาพตามการใช้งาน</textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // ปุ่มรีเซ็ต
    document.querySelector('.btn-reset').addEventListener('click', function() {
      const filterBar = this.closest('.filter-bar');
      filterBar.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
      filterBar.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
      filterBar.querySelectorAll('input[type="date"]').forEach(input => input.value = '');
    });
  </script>

</body>
</html>
