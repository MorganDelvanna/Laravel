@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Additional Family & Cards</h1>
                <h2><span id="memberYear">October 1st, 2025 - September 30th, 2026</span></h2>
            </div>
        </div>
        <form id="form" method="post" action="{{ route('membership.checkout') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="members_json" name="members_json"  value="">
            <input type="hidden" id="photoData" name="photoData" value="">
            <input type="hidden" id="photoName" name="photoName" value="">
            <input type="hidden" id="photoType" name="photoType" value="">
            <p>All required fields must be complete. Applications that are illegible, incomplete or incorrect WILL NOT BE ACCEPTED and money sent will be considered a donation.</p>
            <p>As part of the adding a family member process please submit a photo for each family member to be used for ID. Email your photo(s) to membership@pfga.ca.
                The photo does not need to be professional, it can be taken on your phone. It should look like a passport photo. Please stand in front of a plain, preferably light coloured,
                background and include your head and shoulders. You can smile or not, whichever you prefer. Your face needs to be clearly seen. Thank you. </p>

            <div class="row">
                <div class="col-12">
                    <span><strong>Personal Information:</strong> (Please Complete all fields so we can add new family members or swipe cards to the correct account)</span>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-4">
                    <label for="firstname" class="form-label title required">First Name</label><br />
                    <input id="firstname" type="text" class="form-control new" name="firstname" data-no-auto-fill required>
                </div>
                <div class="col-12 col-md-4">
                    <label for="lastname" class="form-label title required">Last Name</label><br />
                    <input id="lastname" type="text" class="form-control new" name="lastname" data-no-auto-fill required>
                </div>
                <div class="col-12 col-md-4">
                    <label id="cardLabel" for="pfgaNumber" class="form-label title required">PFGA Card #</label><br/>
                    <input id="pfgaNumber" type="text" class="form-control" name="pfgaNumber" data-no-auto-fill required>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12 col-md-4">
                    <label for="email" class="form-label title required">Account E-Mail Address</label>
                    <input type="email" id="email" class="form-control" name="email" required>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <strong>Additional Family Members:</strong> (Please ensure you included the appropriate number of family members in the Membership Fees section below)<br />
                </div>
            </div>
            <div id="familyApp">
            </div>
            <hr>
            <div class="row mt-2">
                <div class="col-12">
                    <strong>Membership Fees:</strong> (Please select the appropriate membership options and add the appropriate fees in the membership dues column). Fees are not refundable.
                </div>
            </div>
            <div class="row">
                <div class="d-none d-sm-block col-md-6">Additional Family Members</div>
                <div class="col-6 d-md-none">Extra Family</div>
                <div class="d-none d-sm-block col-md-2"><span class="familyFee"></span></div>
                <div class="col-2 col-md-2"><input type="number" id="family" name="family" value="0"></div>
            </div>
            <div class="row">
                <div class="d-none d-sm-block col-md-6">Extra Swipe Cards</div>
                <div class="col-6 d-md-none">Extra Swipe Cards</div>
                <div class="d-none d-sm-block col-md-2"><span class="extraFee"></span></div>
                <div class="col-2 col-md-2"><input type="number" id="extra" name="extra" value="0"></div>
            </div>
            <div class="row">
                <div class="col-12"><span><strong>Note:</strong> Extra swipe cards are for adults who will require their own swipe card to access the club</span></div>
            </div>
            <div class="row">
                <div class="col-6 col-md-10 align-content-end"><strong>Amount Due:</strong></div>
                <div class="col-6 col-md-2">&nbsp;$<span id="total"></span></div>
            </div>
            <div class="row mt-2 terms">
                <div class="col-12">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label class="ml-1" for="terms">I agree that I have read the all the instructions and I hereby declare that the information provided is true and correct</label>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-8 col-md-1">
                    <span id="errors" class="error"></span>
                    <button type="button" class="btn btn-primary" id="btnSubmit">Submit</button>
                </div>
            </div>
        </form>
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
        </script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/jquery.inputmask.min.js"></script>
        <script>
            window.MEMBERSHIP_APPLICATION_TYPE = 'update';
        </script>
        <script type="text/javascript" src="{{ asset('js/member.js') }}"></script>
    </div>
@endsection
