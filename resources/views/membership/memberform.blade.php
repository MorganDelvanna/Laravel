@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Membership Application</h1>
                <h2><span id="memberYear">October 1st, 2025 - September 30th, 2026</span></h2>
            </div>
        </div>
        <form id="form" method="post" action="{{ route('membership.checkout') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="members_json" name="members_json"  value="">
            <input type="hidden" id="photoData" name="photoData" value="">
            <input type="hidden" id="photoName" name="photoName" value="">
            <input type="hidden" id="photoType" name="photoType" value="">
            <p>All required fields must be complete. Applications that are illegible, incomplete or incorrect WILL NOT BE ACCEPTED.<br />
                The membership application and payment form must be submitted with appropriate fees for presentation to the Board of Directors. Applications without payment WILL NOT BE ACCEPTED.</p>
            <p>As part of the new member application process please submit a photo for each family member to be used for ID. Email your photo(s) to membership@pfga.ca. The photo does not need to be professional, it can be taken on your phone. It should look like a passport photo. Please stand in front of a plain, preferably light coloured, background and include your head and shoulders. You can smile or not, whichever you prefer. Your face needs to be clearly seen. Thank you. </p>
            <br />
            <div class="row">
                <div class="col-12"><strong>Application Type: </strong>(Please select one of the following options)</div>
            </div>
            <div class="row mt-1">
                <div class="col-12 col-md-4">
                    <input class="form-check-input" type="radio" id="newMember" name="applicationType" value="new" checked> <label class="form-check-label" for="newMember">New Membership (Full Year)</label>
                </div>
                <div class="col-12 col-md-4" id="halfColumn">
                   <input class="form-check-input" type="radio" id="halfMember" name="applicationType" value="half"> <label class="form-check-label" for="halfMember">New Membership (Half Year)*<br />April to September</label>
                </div>
                <div class="col-12 col-md-4">
                    <input class="form-check-input" type="radio" id="renewMember" name="applicationType" value="renew"> <label class="form-check-label" for="renewMember">Membership Renewal</label>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                   <span id="halfSpan">Note: Half-Year Applications are for NEW MEMBERS ONLY who join April 1st - Sept 30th.</span>
                </div>
            </div>

            <hr />

            <div class="row">
                <div class="col-12">
                    <span><strong>Personal Information:</strong> (Please Complete all <span class="required">REQUIRED</span> fields)</span>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-2">
                    <label for="firstname" class="form-label title required">First Name</label><br />
                    <input id="firstname" type="text" class="form-control " name="firstname" required>
                </div>
                <div class="col-12 col-md-2">
                    <label for="lastname" class="form-label title required">Last Name</label><br />
                    <input id="lastname" type="text" class="form-control" name="lastname" required>
                </div>
                <div class="col-12 col-md-2">
                    <label for="alias" class="form-label title">Preferred Name</label><br />
                    <input id="alias" type="text" class="form-control" name="alias">
                </div>
                <div class="col-12 col-md-2">
                    <label for="dob" class="form-label title required new" >Date of Birth</label><br/>
                    <input id="dob" type="date" class="form-control new" name="dob" required>
                </div>
                <div id="cardCell" class="col-12 col-md-3 hidden">
                    <label id="cardLabel" for="pfgaNumber" class="form-label title">PFGA Card #</label><br/>
                    <input id="pfgaNumber" type="text" class="form-control" name="pfgaNumber">
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-3">
                    <label for="address" class="form-label title required new">Home Address</label><br />
                    <input id="address" type="text" class="form-control new" name="address" required>
                </div>
                <div class="col-12 col-md-2" >
                    <label for="city" class="form-label title required new">City</label><br />
                    <input id="city" type="text" class="form-control new" name="city" required>
                </div>
                <div class="col-12 col-md-2">
                    <label for="province" class="form-label title required new">Province</label><br />
                    <input id="province" type="text" class="form-control new" name="province" required>
                </div>
                <div class="col-12 col-md-2">
                    <label for="postal" class="form-label title required new">Postal Code</label><br />
                    <input id="postal" type="text" class="form-control new" name="postal" required>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-2">
                    <label for="homephone" class="form-label title">Home Phone *</label><br/>
                    <input id="homephone" type="text" class="form-control new phone" name="homephone" >
                </div>
                <div class="col-12 col-md-2">
                    <label for="cellphone" class="form-label title">Cell Phone *</label><br />
                    <input id="cellphone" type="text" class="form-control new phone" name="cellphone" >
                </div>
                <div class="col-12 col-md-4">
                    <label for="email" class="form-label title required">E-Mail Address</label>
                    <input type="text" id="email" class="form-control new" name="email" required>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="row">
                        <div class="col-4 centered">
                            <label for="pal" class="form-check-label title required">PAL</label><br />
                            <input class="form-check-input" type="radio" id="pal" name="palType" value="pal" required checked>
                        </div>
                        <div class="col-4 centered">
                            <label for="rpal" class="form-check-label title required">RPAL</label><br />
                            <input class="form-check-input" type="radio" id="rpal" name="palType" value="rpal">
                        </div>
                        <div class="col-4 centered">
                            <label for="noPal" class="form-check-label title required">None</label><br />
                            <input class="form-check-input" type="radio" id="noPal" name="palType" value="noPal">
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <label for="palDate" class="form-label title">Approx. Date of PAL Course (MM-YY)</label>
                    <input type="text" id="palDate" class="form-control" name="palDate">
                </div>
                <div class="col-12 col-md-2">
                    <label for="PALNum" class="form-label title required pal">PAL/RPAL #</label>
                    <input type="text" id="PALNum" class="form-control" name="palNum" required>
                </div>
                <div class="col-12 col-md-2">
                    <label for="palExpiry" class="form-label title required pal">Pal Expiry Date</label>
                    <input type="date" id="palExpiry" class="form-control" name="palExpiry" required>
                </div>
            </div>
            <hr />
            <div class="row mt-2 newOnly">
                <div class="col-12">
                    <span><strong>Please select the section(s) you would like to join:</strong> (New Members Only)</span>
                </div>
                <div class="col-12 col-md-2" style="align-content: center;">
                    <input class="form-check-input" type="checkbox" id="archery" name="archery">
                    <label class="form-check-label ml-1" for="archery">Archery</label>
                </div>
                <div class="col-12 col-md-2" style="align-content: center;">
                    <input class="form-check-input" type="checkbox" id="rifle" name="rifle">
                    <label class="form-check-label ml-1" for="rifle">Rifle</label>
                </div>
                <div class="col-12 col-md-2" style="align-content: center;">
                    <input class="form-check-input" type="checkbox" id="smallbore" name="smallbore">
                    <label class="form-check-label ml-1" for="smallbore">Smallbore</label>
                </div>
                <div class="col-12 col-md-2" style="align-content: center;">
                    <input class="form-check-input" type="checkbox" id="handgun" name="handgun">
                    <label class="form-check-label ml-1" for="handgun">Handgun</label>
                </div>
                <div class="col-12 col-md-2" style="align-content: center;">
                    <input class="form-check-input" type="checkbox" id="action" name="action">
                    <label class="form-check-label ml-1" for="action">Action*</label>
                </div>
                <div class="col-12">
                    <span><strong>Note:</strong> In order to join Action Pistol you must first join Handgun, or be a current active member of an action shooting organization such as IPSC, IDPA, or ICORE. Please include this in the training & certifications section.</span>
                </div>
            </div>
            <hr />
            <div class="row mt-2 newOnly">
                <div class="col-12">
                    <div class="row mt-3">
                        <div class="col-12">
                            <strong>Photo Upload:</strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <label for="photo" class="form-label title required">Passport-style Photo</label><br />
                            <input id="photo" type="file" class="form-control new" name="photo" accept="image/jpeg,image/jpg,image/png">
                            <small class="form-text text-muted">Upload a passport-style JPG or PNG photo, max 200 KB. The image should be portrait orientation with a near passport ratio.</small>
                            <div id="photoError" class="error"></div>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="newOnly" />
            <div class="row mt-2">
                <div class="col-12 col-md-8">
                    <strong>Additional Family Members:</strong> (Please ensure you included the appropriate number of family members in the Membership Fees section below)<br />
                </div>
            </div>

            <div id="familyApp">
            </div>

            <hr />
            <div id="clubsApp" class="newOnly">
            </div>

            <hr class="newOnly" />
            <div id="coursesApp" class="newOnly">
            </div>
            <hr class="newOnly" />
            <div class="row mt-2">
                <div class="col-12">
                    <strong>Membership Fees:</strong> (Please select the appropriate membership options and add the appropriate fees in the membership dues column). Fees are not refundable.
                </div>
            </div>
            <div class="row">
                <div class="col-6"><strong>Membership<span class="d-none d-sm-block"> Options</span></strong></div>
                <div class="d-none d-sm-block col-md-2"><strong>Full Year (Oct 1 - Sept 30)</strong></div>
                <div class="d-none d-sm-block col-md-2"><strong>Half Year (April 1 - Sept 30)</strong></div>
                <div class="col-6 col-md-2"><strong><span class="d-none d-sm-block">Membership </span>Dues</strong></div>
            </div>
            <div class="row">
                <div class="d-none d-sm-block col-md-6">New Member Initiation Fee (New Members Only)</div>
                <div class="col-6 d-md-none">Initiation Fee (<span class="d-md-none initiaionFee">$75</span>)</div>
                <div class="d-none d-sm-block col-md-2"><span class="initiaionFee">$75</span></div>
                <div class="d-none d-sm-block col-md-2"><span class="initiaionFee">$75</span></div>
                <div class="col-6 col-md-2"><span id="initiationFee"></span></div>
            </div>
            <div class="row">
                <div class="d-none d-sm-block col-md-6">General Membership Fee</div>
                <div class="col-6 d-md-none">General Fee</div>
                <div class="d-none d-sm-block col-md-2"><span class="generalFee"></span></div>
                <div class="d-none d-sm-block col-md-2"><span class="generalHalf"></span></div>
                <div class="col-6 col-md-2"><input id="generalBtn" class="form-check-input" type="radio" title="General Membership" name="membershipFee" value="general" checked>&nbsp;<span id="generalFee"></span></div>
            </div>
            <div class="row">
                <div class="d-none d-sm-block col-md-6">Senior Membership Fee (65+)</div>
                <div class="col-6 d-md-none">Senior Fee (65+)</div>
                <div class="d-none d-sm-block col-md-2"><span class="seniorFee"></span></div>
                <div class="d-none d-sm-block col-md-2"><span class="seniorHalf"></span></div>
                <div class="col-6 col-md-2"><input class="form-check-input" type="radio" title="Senior Membership" name="membershipFee" value="senior">&nbsp;<span id="seniorFee"></span></div>
            </div>
            <div class="row">
                <div class="d-none d-sm-block col-md-6">Junior Membership Fee (12-18)</div>
                <div class="col-6 d-md-none">Junior Fee (12-18)</div>
                <div class="d-none d-sm-block col-md-2"><span class="juniorFee"></span></div>
                <div class="d-none d-sm-block col-md-2"><span class="juniorHalf"></span></div>
                <div class="col-6 col-md-2"><input class="form-check-input" type="radio" title="Junior Membership" name="membershipFee" value="junior">&nbsp;<span id="juniorFee"></span></div>
            </div>
            <div class="row">
                <div class="d-none d-sm-block col-md-6">Summer Archery (Outdoor Archery Only - April 1 to Sept 30)</div>
                <div class="col-6 d-md-none">Summer Archery</div>
                <div class="d-none d-sm-block col-md-2">N/A</div>
                <div class="d-none d-sm-block col-md-2"><span class="generalHalf"></span></div>
                <div class="col-6 col-md-2"><input id="archeryBtn" type="radio" title="Archery Membership" name="membershipFee" value="archery" class="form-check-input hidden">&nbsp;<span Id="archeryFee"></span></div>
            </div>
            <div class="row">
                <div class="d-none d-sm-block col-md-6">Additional Family Members<</div>
                <div class="col-6 d-md-none">Extra Family</div>
                <div class="d-none d-sm-block col-md-2"><span class="familyFee"></span></div>
                <div class="d-none d-sm-block col-md-2"><span class="familyFee"></span></div>
                <div class="col-2 col-md-2"><input type="number" id="family" class="form-control" name="family" value="0"></div>
            </div>
            <div class="row">
                <div class="d-none d-sm-block col-md-6">Extra Swipe Cards<br />
                                        <span><strong>Note:</strong> Extra swipe cards are for adults who will require their own swipe card to access the club</span></div>
                <div class="col-6 d-md-none">Extra Swipe Cards</div>
                <div class="d-none d-sm-block col-md-2"><span class="extraFee"></span></div>
                <div class="d-none d-sm-block col-md-2"><span class="extraFee"></span></div>
                <div class="col-2 col-md-2"><input type="number" id="extra" class="form-control" name="extra" value="0"></div>
            </div>
            <div class="row">
                <div class="col-6 col-md-10 align-content-end"><strong>Amount Due:</strong></div>
                <div class="col-6 col-md-2">&nbsp;$<span id="total"></span></div>
            </div>
            <div class="row mt-2">
                <div class="col-6 col-md-10 align-content-end"><strong>Grand Total (All Applicants):</strong></div>
                <div class="col-6 col-md-2"><strong>$<span id="grandTotal">0</span></strong></div>
            </div>
            <div class="row mt-2 terms">
                <div class="col-12">
                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                    <label class="form-check-label ml-1" for="terms">I agree that I have read the all the instructions and I hereby declare that the information provided is true and correct</label>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-8 col-md-4">
                    <span id="errors" class="error"></span>
                    <button type="button" class="btn btn-primary" id="addApplicant">Add Another Applicant</button>
                </div>
            </div>
        </form>
        <hr />
        <div class="row mt-2">
            <div class="col-12">
                <button class="btn btn-primary" type="button" id="btnSubmit">Submit</button>
                <span id="applicantCount">0 applicants added</span>
                <div id="applicantList"></div>
            </div>
        </div>
    </div>
    <script src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/babel-standalone@6.26.0/babel.min.js"></script>
    <script type="text/babel">
        const { useEffect, useState } = React;

        function Field({ label, value, onChange, type = 'text', required = false, placeholder = '' }) {
            return (
                <div className="col-6 col-md-2">
                    <label className={`form-label ${required ? 'required' : ''}`}>{label}</label>
                    <input className="form-control" type={type} value={value || ''} onChange={onChange} required={required} placeholder={placeholder} />
                </div>
            );
        }

        function FamilyMembers() {
            const [members, setMembers] = useState([]);

            useEffect(() => {
                const reset = () => setMembers([]);
                window.addEventListener('membership-form-reset', reset);
                return () => window.removeEventListener('membership-form-reset', reset);
            }, []);

            useEffect(() => {
                const count = document.getElementById('family');
                const serialized = document.getElementById('members_json');
            if (count) {
                count.value = members.length;
                count.defaultValue = members.length;
            }
                if (serialized) serialized.value = JSON.stringify(members);
                if (typeof window.recalc === 'function') window.recalc();
            }, [members]);

            function updateMember(index, field, value) {
                setMembers(current => current.map((member, itemIndex) =>
                    itemIndex === index ? { ...member, [field]: value } : member
                ));
            }

            function handlePhoto(event, index) {
                const file = event.target.files && event.target.files[0];
                if (!file) return;
                const error = event.target.closest('.familyRow').querySelector('.famPhotoError');
                if (!['image/jpeg', 'image/png'].includes(file.type) || file.size > 200 * 1024) {
                    error.textContent = 'Please choose a JPG or PNG image no larger than 200 KB.';
                    return;
                }
                const reader = new FileReader();
                reader.onload = () => {
                    const member = members[index];
                    const first = (member.firstname || 'family').trim().replace(/[^a-z0-9]/gi, '_');
                    const last = (member.lastname || `member${index}`).trim().replace(/[^a-z0-9]/gi, '_');
                    const extension = file.name.split('.').pop().toLowerCase();
                    updateMember(index, 'photoData', reader.result.split(',')[1]);
                    updateMember(index, 'photoName', `${first}_${last}.${extension}`);
                    updateMember(index, 'photoType', file.type);
                    error.textContent = '';
                };
                reader.readAsDataURL(file);
            }

            // Set default dates when adding new family member
            function addFamilyMember() {
                const newMember = { firstname: '', lastname: '', pal: '', palExpiry: '', dob: '' };
                setMembers(current => [...current, newMember]);
            }

            return (
                <div>
                     {members.map((member, index) => (
                        <div className="row mt-2 familyRow" key={index}>
                            <Field label="First Name" value={member.firstname} required onChange={event => updateMember(index, 'firstname', event.target.value)} />
                            <Field label="Last Name" value={member.lastname} required onChange={event => updateMember(index, 'lastname', event.target.value)} />
                            <Field label="PAL#" value={member.pal} onChange={event => updateMember(index, 'pal', event.target.value)} />
                            <Field label="PAL Expiry" type="date" value={member.palExpiry} onChange={event => updateMember(index, 'palExpiry', event.target.value)} />
                            <Field label="Date of Birth" type="date" value={member.dob} required onChange={event => updateMember(index, 'dob', event.target.value)} />
                            <div className="col-12 col-md-1">
                                <button className="btn btn-outline-danger" type="button" onClick={() => setMembers(current => current.filter((_, itemIndex) => itemIndex !== index))}>Delete</button>
                            </div>
                            <div className="col-12">
                                <input className="form-control" type="file" accept="image/jpeg,image/png" onChange={event => handlePhoto(event, index)} />
                                <div className="error famPhotoError"></div>
                                <input type="hidden" name="familyMembers[]" data-react-json={JSON.stringify(member)} value={`${member.firstname || ''} ${member.lastname || ''} DOB: ${member.dob || ''} PAL: ${member.pal || ''} ${member.palExpiry || ''}`} readOnly />
                                <input type="hidden" name="familyPhotoData[]" value={member.photoData || ''} readOnly />
                                <input type="hidden" name="familyPhotoName[]" value={member.photoName || ''} readOnly />
                                <input type="hidden" name="familyPhotoType[]" value={member.photoType || ''} readOnly />
                            </div>
                        </div>
                    ))}
                    <button type="button" className="btn btn-primary" onClick={addFamilyMember}>Add Family</button>
                </div>
            );
        }

        function RepeatableList({ kind, fields }) {
            const [items, setItems] = useState([]);
            useEffect(() => {
                const reset = () => setItems([]);
                window.addEventListener('membership-form-reset', reset);
                return () => window.removeEventListener('membership-form-reset', reset);
            }, []);

            const add = () => {
                const newItem = Object.fromEntries(fields.map(field => [field.name, '']));
                setItems(current => [...current, newItem]);
            };
            const update = (index, field, value) => setItems(current => current.map((item, itemIndex) => itemIndex === index ? { ...item, [field]: value } : item));
            const remove = index => setItems(current => current.filter((_, itemIndex) => itemIndex !== index));
            const serialize = item => kind === 'club'
                ? `${item.name || ''} ${item.city || ''} ${item.from || ''} - ${item.to || ''}`
                : `${item.desc || ''} ${item.trainer || ''} ${item.location || ''} ${item.date || ''}`;

            return (
                <div>
                    {items.map((item, index) => (
                        <div className="row mt-2" key={index}>
                            {fields.map(field => <Field key={field.name} label={field.label} value={item[field.name]} placeholder={field.placeholder} onChange={event => update(index, field.name, event.target.value)} />)}
                            <div className="col-12 col-md-1">
                                <button className="btn btn-outline-danger" type="button" onClick={() => remove(index)}>Delete</button>
                                <input type="hidden" data-react-json={JSON.stringify(item)} name={kind === 'club' ? 'otherClubs[]' : 'courses[]'} value={serialize(item)} readOnly />
                            </div>
                        </div>
                    ))}
                    <button type="button" className="btn btn-primary" onClick={add}>{kind === 'club' ? 'Add Club' : 'Add Training/Certification'}</button>
                </div>
            );
        }

        function mount(rootId, component) {
            const root = document.getElementById(rootId);
            if (root) ReactDOM.createRoot(root).render(component);
        }

        mount('familyApp', <FamilyMembers />);
        mount('clubsApp', <RepeatableList kind="club" fields={[{ name: 'name', label: 'Club Name' }, { name: 'city', label: 'City, Province' }, { name: 'from', label: 'From (MM/YY)', placeholder: 'MM/YY' }, { name: 'to', label: 'To (MM/YY)', placeholder: 'MM/YY' }]} />);
        mount('coursesApp', <RepeatableList kind="course" fields={[{ name: 'desc', label: 'Description' }, { name: 'trainer', label: 'Instructor/Trainer' }, { name: 'location', label: 'Location' }, { name: 'date', label: 'Date (MM/YY)', placeholder: 'MM/YY' }]} />);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/jquery.inputmask.min.js"></script>
    <script>
        window.MEMBERSHIP_APPLICATION_TYPE = @json($applicationType);
    </script>
    <script type="text/javascript" src="{{ asset('js/member.js') }}"></script>
@endsection

