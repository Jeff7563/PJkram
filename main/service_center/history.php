<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ประวัติเคลม - ระบบจัดการฟอร์มส่งเคลม</title>
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/styles-history.css">
</head>
<body>

  <!-- Sidebar -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content">
    
    <!-- Filter Bar -->
    <div class="filter-bar">
      <div class="filter-group">
        <input type="text" placeholder="ค้นหาเอกสาร..." class="form-control min-w-250">
        <select>
          <option value="">สาขา</option>
          <option value="bangkok">กรุงเทพฯ</option>
          <option value="chiangmai">เชียงใหม่</option>
          <option value="khonkaen">ขอนแก่น</option>
        </select>
        <select>
          <option value=""selected>สถานะ</option>
          <option value="***">***</option>
        </select>
        <input type="date" class="date-input" value="2026-03-24">
      </div>
      <div class="filter-group justify-content-end">
        <button class="btn-search">ค้นหา</button>
        <button class="btn-reset">รีเซ็ต</button>
      </div>
    </div>

    <!-- History Grid -->
    <div class="history-grid">
      
      <!-- Card 1 -->
      <div class="history-card">
        <div class="hc-header">
          <div class="hc-header-left">
            <div class="hc-date">25/3/2569</div>
            <div class="hc-doc">เลขที่เอกสาร : TS01-001</div>
          </div>
          <div class="hc-header-right">
            <div class="hc-badge">สถานะ : รอสั่งอะไหล่</div>
            <a href="edit_claim.php?id=TS01-001" class="hc-btn">แก้ไข</a>
          </div>
        </div>
        
        <div class="hc-fields-grid">
          <div class="hc-field-group">
            <div class="hc-label">สาขา :</div>
            <input type="text" class="hc-input" value="สำนักงานใหญ่">
          </div>
          <div class="hc-field-group">
            <div class="hc-label">ประเภทการเคลม :</div>
            <input type="text" class="hc-input" value="รถลูกค้า - เคลมปกติ">
          </div>
          
          <div class="hc-field-group">
            <div class="hc-label">ชื่อผู้ใช้งาน :</div>
            <input type="text" class="hc-input" value="นาย เทส">
          </div>
          <div class="hc-field-group">
            <div class="hc-label">เบอร์โทรศัพท์ :</div>
            <input type="text" class="hc-input" value="08080000">
          </div>
          
          <div class="hc-field-group">
            <div class="hc-label">หมายเลขตัวถัง :</div>
            <input type="text" class="hc-input" value="ABVAXGASZ1AS">
          </div>
          <div class="hc-field-group">
            <div class="hc-label">วันที่สั่งคืนอะไหล่ :</div>
            <input type="text" class="hc-input" value="-">
          </div>
          
          <div class="hc-field-group">
            <div class="hc-label">วันที่ส่งเคลม :</div>
            <input type="text" class="hc-input" value="25/03/2569">
          </div>
        </div>

        <div class="hc-textarea-group">
          <label class="hc-textarea-label">รายละเอียดปัญหาที่ลูกค้าแจ้ง :</label>
          <textarea class="hc-textarea">ลูกค้าแจ้งว่าไฟเลี้ยวไม่ขึ้น</textarea>
        </div>

        <div class="hc-split-textarea">
          <div class="hc-textarea-group">
            <label class="hc-textarea-label">วิธีการแก้ไข :</label>
            <textarea class="hc-textarea">ช่างได้ทำการตรวจสอบและได้เปลี่ยนเซ็นเซอร์เกียร์</textarea>
          </div>
          <div class="hc-textarea-group">
            <label class="hc-textarea-label">สาเหตุของปัญหา :</label>
            <textarea class="hc-textarea">เซ็นเซอร์เกียร์พัง</textarea>
          </div>
        </div>
      </div>

      <!-- Card 2 -->
      <div class="history-card">
        <div class="hc-header">
          <div class="hc-header-left">
            <div class="hc-date">25/3/2569</div>
            <div class="hc-doc">เลขที่เอกสาร : TS01-002</div>
          </div>
          <div class="hc-header-right">
            <div class="hc-badge">สถานะ : รอสั่งอะไหล่</div>
            <a href="edit_claim.php?id=TS01-002" class="hc-btn">แก้ไข</a>
          </div>
        </div>
        
        <div class="hc-fields-grid">
          <div class="hc-field-group">
            <div class="hc-label">สาขา :</div>
            <input type="text" class="hc-input" value="เชียงใหม่">
          </div>
          <div class="hc-field-group">
            <div class="hc-label">ประเภทการเคลม :</div>
            <input type="text" class="hc-input" value="รถลูกค้า - เคลมปกติ">
          </div>
          
          <div class="hc-field-group">
            <div class="hc-label">ชื่อผู้ใช้งาน :</div>
            <input type="text" class="hc-input" value="นาย เทส2">
          </div>
          <div class="hc-field-group">
            <div class="hc-label">เบอร์โทรศัพท์ :</div>
            <input type="text" class="hc-input" value="09090000">
          </div>
          
          <div class="hc-field-group">
            <div class="hc-label">หมายเลขตัวถัง :</div>
            <input type="text" class="hc-input" value="XYZAXGASZ2XZ">
          </div>
          <div class="hc-field-group">
            <div class="hc-label">วันที่สั่งคืนอะไหล่ :</div>
            <input type="text" class="hc-input" value="-">
          </div>
          
          <div class="hc-field-group">
            <div class="hc-label">วันที่ส่งเคลม :</div>
            <input type="text" class="hc-input" value="25/03/2569">
          </div>
        </div>

        <div class="hc-textarea-group">
          <label class="hc-textarea-label">รายละเอียดปัญหาที่ลูกค้าแจ้ง :</label>
          <textarea class="hc-textarea">สตาร์ทไม่ติด แบตเสื่อม</textarea>
        </div>

        <div class="hc-split-textarea">
          <div class="hc-textarea-group">
            <label class="hc-textarea-label">วิธีการแก้ไข :</label>
            <textarea class="hc-textarea">เปลี่ยนแบตเตอรี่ใหม่</textarea>
          </div>
          <div class="hc-textarea-group">
            <label class="hc-textarea-label">สาเหตุของปัญหา :</label>
            <textarea class="hc-textarea">แบตเสื่อมตามอายุ</textarea>
          </div>
        </div>
      </div>

      <!-- Card 3 -->
      <div class="history-card">
        <div class="hc-header">
          <div class="hc-header-left">
            <div class="hc-date">25/3/2569</div>
            <div class="hc-doc">เลขที่เอกสาร : TS01-003</div>
          </div>
          <div class="hc-header-right">
            <div class="hc-badge">สถานะ : รอส่งอะไหล่</div>
            <a href="edit_claim.php" class="hc-btn">แก้ไข</a>
          </div>
        </div>
        
        <div class="hc-fields-grid">
          <div class="hc-field-group">
            <div class="hc-label">สาขา :</div>
            <input type="text" class="hc-input" value="ภูเก็ต">
          </div>
          <div class="hc-field-group">
            <div class="hc-label">ประเภทการเคลม :</div>
            <input type="text" class="hc-input" value="รถลูกค้า - เคลมปกติ">
          </div>
          
          <div class="hc-field-group">
            <div class="hc-label">ชื่อผู้ใช้งาน :</div>
            <input type="text" class="hc-input" value="นาย ใจดี">
          </div>
          <div class="hc-field-group">
            <div class="hc-label">เบอร์โทรศัพท์ :</div>
            <input type="text" class="hc-input" value="08111111">
          </div>
          
          <div class="hc-field-group">
            <div class="hc-label">หมายเลขตัวถัง :</div>
            <input type="text" class="hc-input" value="PKXAXGASZ2XZ">
          </div>
          <div class="hc-field-group">
            <div class="hc-label">วันที่สั่งคืนอะไหล่ :</div>
            <input type="text" class="hc-input" value="-">
          </div>
          
          <div class="hc-field-group">
            <div class="hc-label">วันที่ส่งเคลม :</div>
            <input type="text" class="hc-input" value="25/03/2569">
          </div>
        </div>

        <div class="hc-textarea-group">
          <label class="hc-textarea-label">รายละเอียดปัญหาที่ลูกค้าแจ้ง :</label>
          <textarea class="hc-textarea">เบรกไม่อยู่ มีเสียงดัง</textarea>
        </div>

        <div class="hc-split-textarea">
          <div class="hc-textarea-group">
            <label class="hc-textarea-label">วิธีการแก้ไข :</label>
            <textarea class="hc-textarea">เปลี่ยนผ้าเบรกใหม่ ล้างทำความสะอาดลูกสูบ</textarea>
          </div>
          <div class="hc-textarea-group">
            <label class="hc-textarea-label">สาเหตุของปัญหา :</label>
            <textarea class="hc-textarea">ผ้าเบรกหมด ฝุ่นจับเยอะ</textarea>
          </div>
        </div>
      </div>

      <!-- Card 4 -->
      <div class="history-card">
        <div class="hc-header">
          <div class="hc-header-left">
            <div class="hc-date">25/3/2569</div>
            <div class="hc-doc">เลขที่เอกสาร : TS01-004</div>
          </div>
          <div class="hc-header-right">
            <div class="hc-badge bg-primary-orange text-white">สถานะ : ซ่อมเสร็จสิ้น</div>
            <a href="edit_claim.php?id=TS01-004" class="hc-btn bg-secondary text-white">ดูข้อมูล</a>
          </div>
        </div>
        
        <div class="hc-fields-grid">
          <div class="hc-field-group">
            <div class="hc-label">สาขา :</div>
            <input type="text" class="hc-input" value="โคราช">
          </div>
          <div class="hc-field-group">
            <div class="hc-label">ประเภทการเคลม :</div>
            <input type="text" class="hc-input" value="รถลูกค้า - เคลมพิเศษ">
          </div>
          
          <div class="hc-field-group">
            <div class="hc-label">ชื่อผู้ใช้งาน :</div>
            <input type="text" class="hc-input" value="สมชาย หายห่วง">
          </div>
          <div class="hc-field-group">
            <div class="hc-label">เบอร์โทรศัพท์ :</div>
            <input type="text" class="hc-input" value="08999999">
          </div>
          
          <div class="hc-field-group">
            <div class="hc-label">หมายเลขตัวถัง :</div>
            <input type="text" class="hc-input" value="NKRAXGASZ2XZ">
          </div>
          <div class="hc-field-group">
            <div class="hc-label">วันที่สั่งคืนอะไหล่ :</div>
            <input type="text" class="hc-input" value="26/03/2569">
          </div>
          
          <div class="hc-field-group">
            <div class="hc-label">วันที่ส่งเคลม :</div>
            <input type="text" class="hc-input" value="24/03/2569">
          </div>
        </div>

        <div class="hc-textarea-group">
          <label class="hc-textarea-label">รายละเอียดปัญหาที่ลูกค้าแจ้ง :</label>
          <textarea class="hc-textarea">โช๊คหน้าน้ำมันซึม</textarea>
        </div>

        <div class="hc-split-textarea">
          <div class="hc-textarea-group">
            <label class="hc-textarea-label">วิธีการแก้ไข :</label>
            <textarea class="hc-textarea">เปลี่ยนซีลโช๊คและเติมน้ำมันโช๊คใหม่</textarea>
          </div>
          <div class="hc-textarea-group">
            <label class="hc-textarea-label">สาเหตุของปัญหา :</label>
            <textarea class="hc-textarea">ซีลเสื่อมสภาพตามการใช้งาน</textarea>
          </div>
        </div>
      </div>

    </div>
  </div>
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
