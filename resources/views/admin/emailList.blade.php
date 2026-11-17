@extends('layouts.app')

@section('content')
    <div class="container">        
        <div class="row">
            <div class="col-12">
                <div id="email-list-root"></div>
            </div>
        </div>
    </div>
    <script src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/babel-standalone@6.26.0/babel.min.js"></script>
    <script type="text/babel">
        const { useMemo, useState } = React;
        const initialApplicants = @json($applicants);
        function getPayload(applicant) {
            if (applicant.decrypted_data && typeof applicant.decrypted_data === 'object') return applicant.decrypted_data;
            if (typeof applicant.decrypted_data === 'string') {
                try { return JSON.parse(applicant.decrypted_data); } catch (error) { return {}; }
            }
            if (applicant.data && typeof applicant.data === 'object') return applicant.data;
            if (typeof applicant.data === 'string') {
                try { return JSON.parse(applicant.data); } catch (error) { return {}; }
            }
            return applicant;
        }

        const resendUrlTemplate = @json(route('admin.emailList.resend', ['uuid' => '__UUID__']));
        function EmailList() {
            const applicants = initialApplicants || [];
            const [month, setMonth] = useState(() => {
                const latest = applicants[0] && applicants[0].created_at;
                const date = latest ? new Date(latest) : new Date();
                return new Date(date.getFullYear(), date.getMonth(), 1);
            });
            const [sending, setSending] = useState(null);
            const [notice, setNotice] = useState('');
            const resend = async uuid => {
                setSending(uuid);
                setNotice('');
                try {
                    const response = await fetch(resendUrlTemplate.replace('__UUID__', encodeURIComponent(uuid)), {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json' }
                    });
                    const result = await response.json();
                    setNotice(result.message || (response.ok ? 'Email resent.' : 'Could not resend email.'));
                } catch (error) {
                    setNotice('Could not resend email.');
                } finally { setSending(null); }
            };
            const monthApplicants = useMemo(() => applicants.filter(applicant => {
                const date = new Date(applicant.created_at);
                return date.getFullYear() === month.getFullYear() && date.getMonth() === month.getMonth();
            }), [applicants, month]);

            const moveMonth = amount => setMonth(current => new Date(current.getFullYear(), current.getMonth() + amount, 1));
            const label = month.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
            const adjacentLabel = amount => new Date(month.getFullYear(), month.getMonth() + amount, 1)
                .toLocaleDateString(undefined, { month: 'long', year: 'numeric' });

            return (
                <div className="sr-root">
                    <div className="sr-main">
                        <h1>Applicants without email records</h1>
                        {notice && <div className="alert alert-info" role="status">{notice}</div>}
                        <section className="sr-section mt-3">
                            <div className="d-flex justify-content-between align-items-center mb-2">
                                <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => moveMonth(-1)}>&laquo; {adjacentLabel(-1)}</button>
                                <strong>{label}</strong>
                                <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => moveMonth(1)}>{adjacentLabel(1)} &raquo;</button>
                            </div>
                            <table className="table table-sm table-striped">
                                <thead><tr><th>Form Submitted</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Payment Status</th><th>Action</th></tr></thead>
                                <tbody>
                                    {monthApplicants.length === 0 && <tr><td colSpan="6" className="text-center">No records for this month.</td></tr>}
                                    {monthApplicants.map(applicant => {
                                        const payload = getPayload(applicant);
                                        return <tr key={applicant.uuid}>
                                            <td>{applicant.created_at || ''}</td>
                                            <td>{payload.firstname || ''}</td>
                                            <td>{payload.lastname || ''}</td>
                                            <td>{payload.email || ''}</td>
                                            <td>{applicant.transaction_id == null ? 'Not Paid' : 'Paid'}</td>
                                            <td><button type="button" className="btn btn-sm btn-outline-primary" disabled={sending === applicant.uuid || applicant.transaction_id == null} onClick={() => resend(applicant.uuid)}>{sending === applicant.uuid ? 'Sending…' : 'Resend Email'}</button></td>
                                        </tr>;
                                    })}
                                </tbody>
                            </table>
                        </section>
                    </div>
                </div>
            );
        }

        ReactDOM.createRoot(document.getElementById('email-list-root')).render(<EmailList />);
    </script>
@endsection
