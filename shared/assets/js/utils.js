/**
 * PJclaim Shared Utilities
 * Centralized logic for car age, dropdown loading, and toast notifications.
 */

const PJUtils = {
    /**
     * Calculate and display vehicle age in real-time
     * @param {string} saleDateStr - YYYY-MM-DD
     * @returns {string} - Human readable age string
     */
    calculateAge: function(saleDateStr) {
        if (!saleDateStr) return '-- ปี -- เดือน -- วัน 0 ชั่วโมง';
        
        const saleDate = new Date(saleDateStr);
        const now = new Date();
        
        if (now < saleDate) return 'วันที่ขายต้องไม่เกินวันปัจจุบัน';

        let years = now.getFullYear() - saleDate.getFullYear();
        let months = now.getMonth() - saleDate.getMonth();
        let days = now.getDate() - saleDate.getDate();
        let hours = now.getHours() - saleDate.getHours();
        let minutes = now.getMinutes() - saleDate.getMinutes();

        if (minutes < 0) { minutes += 60; hours--; }
        if (hours < 0) { hours += 24; days--; }
        if (days < 0) {
            const previousMonth = new Date(now.getFullYear(), now.getMonth(), 0);
            days += previousMonth.getDate();
            months--;
        }
        if (months < 0) { months += 12; years--; }

        let result = [];
        if (years > 0) result.push(`${years} ปี`);
        if (months > 0 || years > 0) result.push(`${months} เดือน`);
        result.push(`${days} วัน`);
        result.push(`${hours} ชม.`);
        result.push(`${minutes} นาที`);

        return result.join(' ');
    },

    /**
     * Load branches into a <select> element
     * @param {string} selectId - ID of the target select element
     * @param {string} currentVal - (Optional) Current value to select
     */
    loadBranches: async function(selectId, currentVal = '') {
        const select = document.getElementById(selectId);
        if (!select) return;
        
        try {
            const res = await fetch('../backend/api_branches.php');
            const json = await res.json();
            if (json.success) {
                // Clear existing (except first)
                while (select.options.length > 1) select.remove(1);
                
                json.data.forEach(b => {
                    const opt = document.createElement('option');
                    opt.value = b.branch_name;
                    opt.textContent = b.branch_name;
                    if (b.branch_name === currentVal) opt.selected = true;
                    select.appendChild(opt);
                });
            }
        } catch (e) {
            console.error('Failed to load branches', e);
        }
    },

    /**
     * Load employees into <select> elements with .employee-select class
     * Automatically handles name and signature fields based on data-target-* attributes.
     */
    loadEmployees: async function() {
        const selects = document.querySelectorAll('.employee-select');
        if (selects.length === 0) return;

        try {
            const res = await fetch('../backend/api_users.php');
            const json = await res.json();
            if (json.success) {
                const employees = json.data;
                selects.forEach(sel => {
                    const currentVal = sel.getAttribute('data-current') || sel.value;
                    sel.innerHTML = '<option value="">-- เลือกพนักงาน --</option>';
                    
                    employees.forEach(emp => {
                        const opt = document.createElement('option');
                        opt.value = emp.employee_id;
                        opt.textContent = `${emp.employee_id} - ${emp.name}`;
                        if (emp.employee_id === currentVal) opt.selected = true;
                        sel.appendChild(opt);
                    });

                    sel.addEventListener('change', function() {
                        const targetName = document.getElementsByName(this.dataset.targetName)[0] || document.getElementById(this.dataset.targetName);
                        const targetSig = document.getElementsByName(this.dataset.targetSig)[0] || document.getElementById(this.dataset.targetSig);
                        const emp = employees.find(e => e.employee_id === this.value);
                        
                        if (targetName) targetName.value = emp ? emp.name : '';
                        if (targetSig) targetSig.value = emp ? (emp.signature || 'No Signature') : '';
                    });
                });
            }
        } catch (e) {
            console.error('Failed to load employees', e);
        }
    },

    /**
     * Simple Toast Notification helper
     */
    toast: function(message, type = 'success') {
        if (window.showToast) {
            window.showToast(message, type);
        } else {
            console.log(`[Toast ${type}] ${message}`);
            alert(message);
        }
    }
};

// Export to window
window.PJUtils = PJUtils;
