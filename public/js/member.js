// Fee constants loaded from PHP environment variables
// These are injected as a script block in memberform.php
let general = window.MEMBERSHIP_FEES?.general || 350;
let senior = window.MEMBERSHIP_FEES?.senior || 320;
let junior = window.MEMBERSHIP_FEES?.junior || 250;
let halfGeneral = window.MEMBERSHIP_FEES?.halfGeneral || 250;
let halfSenior = window.MEMBERSHIP_FEES?.halfSenior || 230;
let halfJunior = window.MEMBERSHIP_FEES?.halfJunior || 180;
let initiation = window.MEMBERSHIP_FEES?.initiation || 75;
let extraCards = window.MEMBERSHIP_FEES?.extraCards || 25;
let family = window.MEMBERSHIP_FEES?.family || 20;

// Promise wrapper for readPhotoFile
function readPhotoFilePromise(file) {
    return new Promise(function(resolve){
        readPhotoFile(file, function(result){ resolve(result); });
    });
}

function validatePhotoFile(file) {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    const maxSize = 200 * 1024;
    if (!file) {
        return { ok: false, message: 'Please select a photo.' };
    }
    if (!allowedTypes.includes(file.type)) {
        return { ok: false, message: 'Photo must be JPG or PNG.' };
    }
    if (file.size > maxSize) {
        return { ok: false, message: 'Photo must be 200 KB or smaller.' };
    }
    return { ok: true, message: '' };
}

function readPhotoFile(file, callback) {
    const validation = validatePhotoFile(file);
    if (!validation.ok) {
        return callback(validation);
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const dataUrl = e.target.result;
        const img = new Image();
        img.onload = function() {
            const ratio = img.width / img.height;
            if (ratio < 0.75 || ratio > 0.85) {
                return callback({ ok: false, message: 'Photo should be portrait orientation and roughly passport ratio.' });
            }
            callback({ ok: true, dataUrl, name: file.name, type: file.type });
        };
        img.onerror = function() {
            callback({ ok: false, message: 'Unable to read the selected photo.' });
        };
        img.src = dataUrl;
    };
    reader.onerror = function() {
        callback({ ok: false, message: 'Unable to read the selected photo.' });
    };
    reader.readAsDataURL(file);
}

function setPhotoData(dataUrl, file) {
    var firstName = $('#firstname').val().trim().replace(/[^a-z0-9]/gi, '_');
    var lastName = $('#lastname').val().trim().replace(/[^a-z0-9]/gi, '_');
    var ext = file.name.split('.').pop();
    var newFileName = firstName + '_' + lastName + '.' + ext;

    $('#photoData').val(dataUrl.replace(/^data:[^;]+;base64,/, ''));
    $('#photoName').val(newFileName);
    $('#photoType').val(file.type);

}

function clearPhotoFields() {
    $('#photo').val('');
    $('#photoData').val('');
    $('#photoName').val('');
    $('#photoType').val('');
    $('#photoError').text('');
}

function handlePhotoChange() {
    const input = $('#photo')[0];
    const file = input?.files?.[0];
    if (!file) {
        clearPhotoFields();
        return;
    }
    readPhotoFile(file, function (result) {
        if (!result.ok) {
            clearPhotoFields();
            $('#photoError').text(result.message);
            return;
        }
        setPhotoData(result.dataUrl, file);
        $('#photoError').text('');
    });
}

function recalc() {
    let selectedValue = $('input[name="applicationType"]:checked').val();
    let totalInitiation;
    switch (selectedValue) {
        case "new":
        case "half":
            totalInitiation = initiation;
            break;
        case "renew":
            totalInitiation = 0;
            break;
        default:
            totalInitiation = 0;
            break;
    }
    let totalFee;

        let selectedFee = $('input[name="membershipFee"]:checked').val();
        switch (true) {
            case (selectedFee == "general" && selectedValue == "new"):
            case (selectedFee == "general" && selectedValue == "renew"):
                totalFee = general;
                break;
            case (selectedFee == "general" && selectedValue == "half"):
            case (selectedFee == "archery" && selectedValue == "half"):
            case (selectedFee == "archery" && selectedValue == "renew"):
                totalFee = halfGeneral;
                break;
            case (selectedFee == "senior" && selectedValue == "new"):
            case (selectedFee == "senior" && selectedValue == "renew"):
                totalFee = senior;
                break;
            case (selectedFee == "senior" && selectedValue == "half"):
                totalFee = halfSenior;
                break;
            case (selectedFee == "junior" && selectedValue == "new"):
            case (selectedFee == "junior" && selectedValue == "renew"):
                totalFee = junior;
                break;
            case (selectedFee == "junior" && selectedValue == "half"):
                totalFee = halfJunior;
                break;
            default:
                totalFee = 0;
        }



    let totalFam = parseInt($('#family').val()) * family;
    let totalExtra = parseInt($('#extra').val()) * extraCards;

    $('#total').text(totalInitiation + totalFee + totalFam + totalExtra);
    updateGrandTotal();
}

// Calculate fee for a single applicant object
function calculateApplicantFee(applicant) {
    let totalInitiation = 0;
    let totalFee = 0;
    let totalFam = 0;
    let totalExtra = 0;

    // Initiation fee
    if (applicant.applicationType === 'new' || applicant.applicationType === 'half') {
        totalInitiation = initiation;
    }

    // Membership fee
    switch (true) {
        case (applicant.membershipFee == "general" && applicant.applicationType == "new"):
        case (applicant.membershipFee == "general" && applicant.applicationType == "renew"):
            totalFee = general;
            break;
        case (applicant.membershipFee == "general" && applicant.applicationType == "half"):
        case (applicant.membershipFee == "archery" && applicant.applicationType == "half"):
        case (applicant.membershipFee == "archery" && applicant.applicationType == "renew"):
            totalFee = halfGeneral;
            break;
        case (applicant.membershipFee == "senior" && applicant.applicationType == "new"):
        case (applicant.membershipFee == "senior" && applicant.applicationType == "renew"):
            totalFee = senior;
            break;
        case (applicant.membershipFee == "senior" && applicant.applicationType == "half"):
            totalFee = halfSenior;
            break;
        case (applicant.membershipFee == "junior" && applicant.applicationType == "new"):
        case (applicant.membershipFee == "junior" && applicant.applicationType == "renew"):
            totalFee = junior;
            break;
        case (applicant.membershipFee == "junior" && applicant.applicationType == "half"):
            totalFee = halfJunior;
            break;
        default:
            totalFee = 0;
    }

    // Family and extra
    totalFam = (applicant.familyCount || 0) * family;
    totalExtra = (applicant.extra || 0) * extraCards;

    return totalInitiation + totalFee + totalFam + totalExtra;
}

// Calculate grand total for all applicants (stored + current form)
function calculateGrandTotal() {
    // Grand total should include only applicants that have been added to the list
    let total = 0;
    applicants.forEach(function(app) {
        total += calculateApplicantFee(app);
    });
    return total;
}

// Update grand total display
function updateGrandTotal() {
    let grandTotal = calculateGrandTotal();
    $('#grandTotal').text(grandTotal);
}

var applicationTypeChange = function() {
    let selectedValue = $('input[name="applicationType"]:checked').val()
        || window.MEMBERSHIP_APPLICATION_TYPE;
    switch (selectedValue) {
        case "new":
            if (!$('label.new').first().hasClass('required')) {
                $('label.new').addClass('required');
                $('input.new').attr('required');
            }
            $('#cardCell').hide();
            $('#cardLabel').removeClass('required');
            $('#pfgaNumber').removeAttr('required');
            $('.newOnly').show();
            if ($('#archeryBtn').is(':checked')) {
                $('#generalBtn').prop("checked", true);
                $('[name="membershipFee"]').trigger('change');
            }
            $('#archeryBtn').hide();
            $('#initiationFee').text(`$${initiation}`);
            break;
        case "half":
            if (!$('label.new').first().hasClass('required')) {
                $('label.new').addClass('required');
                $('input.new').attr('required');
            }
            $('#cardLabel').removeClass('required');
            $('#pfgaNumber').removeAttr('required');
            $('.newOnly').show();
            $('#cardCell').hide();
            $('#archeryBtn').show();
            $('#initiationFee').text(`$${initiation}`);
            break;
        case "renew":
            $('label.new').removeClass('required');
            $('input.new').removeAttr('required');
            $('#cardCell').show();
            $('#cardLabel').addClass('required');
            $('#pfgaNumber').attr('required');
            $('.newOnly').hide();
            $('#initiationFee').text(`$0`);
            $('#archeryBtn').show();
            break;
    }
    recalc();
}

// Multi-applicant support
let applicants = [];

function gatherApplicantObject() {
    // collect applicant main fields
    let applicant = {};
    const urlParams = new URLSearchParams(window.location.search);
    applicant.applicationType = $('input[name="applicationType"]:checked').val()
        || window.MEMBERSHIP_APPLICATION_TYPE
        || urlParams.get('type')
        || 'new';
    applicant.membershipFee = $('input[name="membershipFee"]:checked').val() || '';
    applicant.firstname = $('#firstname').val() || '';
    applicant.lastname = $('#lastname').val() || '';
    applicant.alias = $('#alias').val() || '';
    applicant.dob = $('#dob').val() || '';
    applicant.pfgaNumber = $('#pfgaNumber').val() || '';
    applicant.address = $('#address').val() || '';
    applicant.city = $('#city').val() || '';
    applicant.province = $('#province').val() || '';
    applicant.postal = $('#postal').val() || '';
    applicant.homephone = $('#homephone').val() || '';
    applicant.cellphone = $('#cellphone').val() || '';
    applicant.email = $('#email').val() || '';
    applicant.palType = $('input[name="palType"]:checked').val() || '';
    applicant.palDate = $('#palDate').val() || '';
    applicant.palNum = $('#PALNum').val() || '';
    applicant.palExpiry = $('#palExpiry').val() || '';
    applicant.disciplines = [];
    if($('#archery').is(':checked')) applicant.disciplines.push('archery');
    if($('#rifle').is(':checked')) applicant.disciplines.push('rifle');
    if($('#smallbore').is(':checked')) applicant.disciplines.push('smallbore');
    if($('#handgun').is(':checked')) applicant.disciplines.push('handgun');
    if($('#action').is(':checked')) applicant.disciplines.push('action');
    function collectReactItems(selector) {
        return $(selector).map(function () {
            try {
                return JSON.parse($(this).attr('data-react-json'));
            } catch (e) {
                return null;
            }
        }).get().filter(Boolean);
    }

    applicant.family = collectReactItems('[name="familyMembers[]"][data-react-json]').map(function (member) {
        return {
            firstname: member.firstname || '',
            lastname: member.lastname || '',
            pal: member.pal || '',
            palExpiry: member.palExpiry || '',
            dob: member.dob || '',
            photo: member.photoData ? {
                name: member.photoName || '',
                type: member.photoType || '',
                data: member.photoData
            } : null
        };
    });
    applicant.clubs = collectReactItems('[name="otherClubs[]"][data-react-json]');
    applicant.courses = collectReactItems('[name="courses[]"][data-react-json]');

    applicant.extra = parseInt($('#extra').val()) || 0;
    applicant.familyCount = parseInt($('#family').val()) || 0;
    applicant.terms = $('#terms').is(':checked');

    const photoData = $('#photoData').val();
    if (photoData) {
        applicant.photo = {
            name: $('#photoName').val() || '',
            type: $('#photoType').val() || '',
            data: photoData
        };
    }

    return applicant;
}

function clearForm() {
    $('#form')[0].reset();
    window.dispatchEvent(new Event('membership-form-reset'));
    setApplicationType();
    $('#family').val(0);
    clearPhotoFields();
    recalc();
    updateGrandTotal();
}

function renderApplicants() {
    $('#applicantCount').text(applicants.length + ' applicants added').attr('data-count', applicants.length);
    let html = '<ul>';
    applicants.forEach(function(a, i){
        let fee = calculateApplicantFee(a);
        html += `<li>${i+1}: ${a.firstname} ${a.lastname} (${a.email || 'no email'}) — ${a.family.length} family members — $${fee}</li>`;
    });
    html += '</ul>';
    $('#applicantList').html(html);
    updateGrandTotal();
}

function setApplicationType(){
    let urlParams = new URLSearchParams(window.location.search);
    let applicationType = window.MEMBERSHIP_APPLICATION_TYPE || urlParams.get('type') || 'new';
    switch (applicationType){
            case "update":
                break;
            case "renew":
                $('#renewMember').prop("checked", true);
                break;
            case "new":
                $('#newMember').prop("checked", true);
                break;
            case "half":
                $('#halfMember').prop("checked", true);
                break;
            default:
                $('#newMember').prop("checked", true);
    }
    applicationTypeChange();
}


$(function(){
    $('.initiationFee').text(`$${initiation}`);
    $('#initiationFee').text(`$${initiation}`);
    $('#generalFee').text(`$${general}`);
    $('.generalFee').text(`$${general}`);
    $('.generalHalf').text(`$${halfGeneral} (New Members Only)`);
    $('.seniorFee').text(`$${senior}`);
    $('.seniorHalf').text(`$${halfSenior}`);
    $('.juniorFee').text(`$${junior}`);
    $('.juniorHalf').text(`$${halfJunior}`);
    $('.familyFee').text(`$${family} each`);
    $('.extraFee').text(`$${extraCards} each`);
    $('#homephone').inputmask("999-999-9999");
    $('#cellphone').inputmask("999-999-9999");
    $('#photo').on('change', handlePhotoChange);

    var $inputs = $('input[name=homephone],input[name=cellphone]');
    $inputs.on('input', function () {
        // Set the required property of the other input to false if this input is not empty.
        $inputs.not(this).prop('required', !$(this).val().length);
    });

    let date = new Date();
    const year = date.getFullYear();

    // Returns the Date of the first Tuesday of a given month (0 = Jan)
    function firstTuesday(year, month) {
        const d = new Date(year, month, 1);
        const daysUntilTuesday = (2 - d.getDay() + 7) % 7; // Tuesday = 2
        d.setDate(1 + daysUntilTuesday);
        return d;
    }

    const firstTuesdayMay = firstTuesday(year, 4);   // May
    const firstTuesdayJuly = firstTuesday(year, 6);  // July

    if (date >= firstTuesdayMay && date < firstTuesdayJuly) {
        $('#halfColumn').show();
        $('#halfSpan').show();
    } else {
        $('#halfColumn').hide();
        $('#halfSpan').hide();
    }

    if (date.getMonth() >= 7 && date.getMonth() <= 11) {
        $('#memberYear').text(`October 1st ${date.getFullYear()} - September 30th ${date.getFullYear() + 1}`);
    } else {
        $('#memberYear').text(`October 1st ${date.getFullYear() - 1} - September 30th ${date.getFullYear()}`);
    }


    $('[name="applicationType"]').on("change", applicationTypeChange);

    $('[name="palType"]').on('change', function(){
        let selectedValue = $('[name="palType"]:checked').val();
        let PALNum = $('#PALNum');
        let palExpiry = $('#palExpiry');

        switch(selectedValue){
            case "pal":
            case "rpal":
                if(!PALNum.hasClass('required')){
                    PALNum.attr('required');
                    palExpiry.attr('required');
                    $('label.pal').addClass('required');
                }
                break;
            case "noPal":
                PALNum.removeAttr('required');
                palExpiry.removeAttr('required');
                $('label.pal').removeClass('required');
        }
    });

    $('[name="membershipFee"]').on("change", function(){
        let selectedValue = $('input[name="membershipFee"]:checked').val();
        let selectedType = $('input[name="applicationType"]:checked').val();
        switch (true) {
            case ((selectedValue == "general") && (selectedType == "new")):
            case ((selectedValue == "general") && (selectedType == "renew")):
                $('#generalFee').text(`$${general}`);
                $('#seniorFee').text("");
                $('#juniorFee').text("");
                $('#archeryFee').text("");
                break;
            case ((selectedValue == "general") && (selectedType == "half")):
                $('#generalFee').text(`$${halfGeneral}`);
                $('#seniorFee').text("");
                $('#juniorFee').text("");
                $('#archeryFee').text("");
                break;
            case ((selectedValue == "senior") && (selectedType == "new")):
            case ((selectedValue == "senior") && (selectedType == "renew")):
                $('#seniorFee').text(`$${senior}`);
                $('#generalFee').text("");
                $('#juniorFee').text("");
                $('#archeryFee').text("");
                break;
            case ((selectedValue == "senior") && (selectedType == "half")):
                $('#seniorFee').text(`$${halfSenior}`);
                $('#generalFee').text("");
                $('#juniorFee').text("");
                $('#archeryFee').text("");
                break;
            case ((selectedValue == "junior") && (selectedType == "new")):
            case ((selectedValue == "junior") && (selectedType == "renew")):
                $('#seniorFee').text("");
                $('#generalFee').text("");
                $('#juniorFee').text(`$${junior}`);
                $('#archeryFee').text("");
                break;
            case ((selectedValue == "junior") && (selectedType == "half")):
                $('#seniorFee').text("");
                $('#generalFee').text("");
                $('#juniorFee').text(`$${halfJunior}`);
                $('#archeryFee').text("");
                break;
            case ((selectedValue == "archery") && (selectedType == "half")):
            case ((selectedValue == "archery") && (selectedType == "renew")):
                $('#seniorFee').text("");
                $('#generalFee').text("");
                $('#juniorFee').text("");
                $('#archeryFee').text(`$${halfGeneral}`);
                break;
        }
        recalc();
    });

    $('#family').on("change", function() { recalc() });
    $('#extra').on("change", function() { recalc() });
    recalc();

    $.validator.addMethod("familyTest", function(value, element){
        return $('.familyRow').length == value;
    }, "The number of family members does not match the number being paid for");

    // Helper to wait for any pending family photo reads to finish
    function waitForFamilyPhotos() {
        return Promise.resolve();
    }


    // Add Applicant button: capture current filled form as one applicant and clear for next
    $('#addApplicant').on('click', async function(){
        // Wait for any family photo reads to complete
        await waitForFamilyPhotos();
        if (!$('#form').valid()) {
            return;
        }
        let app = gatherApplicantObject();
        // basic required check
        if (!app.firstname || !app.lastname || !app.email) {
            alert('Applicant must include first name, last name, and email');
            return;
        }
        if ((!app.photo || !app.photo.data) && app.applicationType !== 'renew') {
            alert('Please upload a valid passport-style photo before adding an applicant.');
            return;
        }
        applicants.push(app);
        renderApplicants();
        clearForm();
    });

    function submitApplicants(form) {
        const currentApp = gatherApplicantObject();
        const hasCurrentApplicant = [currentApp.firstname, currentApp.lastname, currentApp.email]
            .some(value => value && value.trim() !== '');
        const checkoutApplicants = applicants.slice();

        if (hasCurrentApplicant || applicants.length === 0) {
            if (!$('#form').valid()) {
                return;
            }
            if ((!currentApp.photo || !currentApp.photo.data) && currentApp.applicationType === 'new') {
                alert('Please upload a valid passport-style photo for the current applicant before submitting.');
                return;
            }
            checkoutApplicants.push(currentApp);
        }

        // Keep the editable applicant out of the saved list, including on retries.
        $('#members_json').val(JSON.stringify(checkoutApplicants));
        $('#errors').text('');
        form.submit();
    }

    $('#btnSubmit').on("click", async function(){
        await waitForFamilyPhotos();
        recalc();
        submitApplicants($('#form')[0]);
    });

    $('#form').validate({
        rules: {
            family: { familyTest : true },
            homephone: {
                require_from_group: [1, '.phone']
            },
            cellphone: {
                require_from_group: [1, '.phone']
            },
            terms: {
                required: true
            }
        },
        messages: {
            family: "The number of family members does not match the number being paid for",
            homephone: "Home Phone or Cell Phone is required",
            cellphone: "Home Phone or Cell Phone is required",
            terms: "You must agree to the terms and conditions"
        },
        highlight: function(element) {
                    $(element).addClass('is-invalid');
                    $(element).closest('.form-group').addClass('has-error');
                },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid');
            $(element).closest('.form-group').removeClass('has-error');
        },
        submitHandler: function(form) {
            submitApplicants(form);
        },
        invalidHandler: function(event, validator){
            let errors = validator.numberOfInvalids();
            if (errors){
                $('#errors').text("Something isn't right");
            }
        },
        errorClass: "error"
    });

    setApplicationType();
    updateGrandTotal();
});
