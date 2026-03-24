(function(){
  const form = document.getElementById('claimForm');
  const carTypeRadios = document.getElementsByName('carType');
  const partsTableBody = document.querySelector('#partsTable tbody');
  const addPartBtn = document.getElementById('addPart');
  const resultBox = document.getElementById('result');

  function addPartRow(name='',qty='1'){
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input required name="parts_name[]" class="part-name" placeholder="รายการอะไหล่" value="${name}"></td>
      <td style="width:120px"><input required name="parts_qty[]" type="number" min="1" class="part-qty" value="${qty}"></td>
      <td style="width:60px"><button type="button" class="btn remove">ลบ</button></td>
    `;
    tr.querySelector('.remove').addEventListener('click',()=>tr.remove());
    partsTableBody.appendChild(tr);
  }

  addPartBtn.addEventListener('click',()=>addPartRow());
  // seed one row
  addPartRow();

  // image preview helpers
  function previewFiles(inputEl, containerEl){
    const files = inputEl.files;
    containerEl.innerHTML = '';
    if(!files || files.length===0) return;
    Array.from(files).forEach(f=>{
      const reader = new FileReader();
      reader.onload = e=>{
        const img = document.createElement('img');
        img.src = e.target.result;
        containerEl.appendChild(img);
      };
      reader.readAsDataURL(f);
    })
  }

  // wire image inputs
  const images = [
    ['imgFullCar','previewFullCar'],
    ['imgSpot','previewSpot'],
    ['imgPart','previewPart'],
    ['imgWarranty','previewWarranty'],
    ['imgOdometer','previewOdometer'],
    ['imgEstimate','previewEstimate']
  ];
  images.forEach(([inp,pre])=>{
    const i = document.getElementById(inp);
    const p = document.getElementById(pre);
    if(i && p){
      i.addEventListener('change',()=>previewFiles(i,p));
    }
  });

  // show/hide extra fields for used car if needed (example extension point)
  function handleCarTypeChange(){
    const type = Array.from(carTypeRadios).find(r=>r.checked).value;
    // currently no extra fields to show/hide, this is where we'd toggle sections
    // For demo, when "used" selected, highlight odometer field requirement
    const odometer = document.getElementById('imgOdometer');
    if(type==='used'){
      odometer.setAttribute('required','required');
    } else {
      odometer.removeAttribute('required');
    }
  }
  carTypeRadios.forEach(r=>r.addEventListener('change',handleCarTypeChange));
  handleCarTypeChange();

  // gather form data and validate minimal requirements
  form.addEventListener('submit',e=>{
    e.preventDefault();
    // simple validation example
    const requiredFields = ['branch','claimDate','vin','ownerName','problemDesc','recorder'];
    for(const id of requiredFields){
      const el = document.getElementById(id);
      if(!el || !el.value.trim()){
        el && el.focus();
        showResult('กรุณากรอกข้อมูลที่จำเป็น: '+(el?el.previousSibling.textContent.trim()||id:id),'error');
        return;
      }
    }

    const parts = Array.from(partsTableBody.querySelectorAll('tr')).map(tr=>({
      name: tr.querySelector('.part-name').value.trim(),
      qty: Number(tr.querySelector('.part-qty').value) || 0
    })).filter(p=>p.name);

    const partsDeliveryEl = document.querySelector('input[name="partsDelivery"]:checked');
    const partsDeliveryValue = partsDeliveryEl ? partsDeliveryEl.value : '';

    const payload = {
      branch: document.getElementById('branch').value,
      claimDate: document.getElementById('claimDate').value,
      carType: Array.from(carTypeRadios).find(r=>r.checked).value,
      carBrand: document.getElementById('carBrand')?document.getElementById('carBrand').value:'',
      vin: document.getElementById('vin').value.trim(),
      ownerName: document.getElementById('ownerName').value.trim(),
      problemDesc: document.getElementById('problemDesc').value.trim(),
      inspectMethod: document.getElementById('inspectMethod').value.trim(),
      inspectCause: document.getElementById('inspectCause').value.trim(),
      claimCategory: document.getElementById('claimCategory').value,
      repairBranch: document.getElementById('repairBranch').checked,
      sendHQ: document.getElementById('sendHQ').checked,
      parts, partsDelivery: document.getElementById('partsDelivery').value,
      recorder: document.getElementById('recorder').value.trim()
    };

    // for demo we just log the payload and show success
    console.log('Claim payload',payload);
    showResult('บันทึกการส่งเคลมเรียบร้อย (ดู console สำหรับข้อมูลที่ส่ง)', 'success');
    form.reset();
    // clear previews and parts table
    document.querySelectorAll('.preview').forEach(p=>p.innerHTML='');
    partsTableBody.innerHTML=''; addPartRow();
    handleCarTypeChange();
  });

  function showResult(msg,type){
    resultBox.textContent = msg;
    if(type==='success'){
      resultBox.className='result success';
    } else {
      resultBox.className='result';
      resultBox.style.background='#d9534f';
      resultBox.style.display='block';
    }
  }
})();