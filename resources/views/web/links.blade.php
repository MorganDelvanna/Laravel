@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Shooting Links</h1>
        <div id="links-root"></div>
    </div>

    <script type="text/babel">
        const { useEffect, useState } = React;

        const categories = [
            { key: 'Dealers', label: 'Gun Dealers' },
            { key: 'Clubs', label: 'Local Gun Clubs' },
            { key: 'Manufacturer', label: 'Gun Manufacturers' },
            { key: 'Organizations', label: 'Shooting Organizations (Canadian and U.S.)' }
        ];

        function LinkPanel({ category, links, open, onToggle }) {
            const categoryLinks = links
                .filter(link => link.Category === category.key)
                .sort((first, second) => (first.Title || '').localeCompare(second.Title || ''));

            return (
                <section className="panel panel-default">
                    <h2 className="panel-heading pfgaAccordian">
                        <button
                            className="pfgaAccordian w-100 text-start border-0"
                            type="button"
                            aria-expanded={open}
                            onClick={onToggle}
                        >
                            {category.label}
                        </button>
                    </h2>
                    {open && (
                        <div className="panel-body">
                            <div className="row">
                                {categoryLinks.map((link, index) => (
                                    <div className="padded col-12 col-md-4" key={`${link.URL}-${index}`}>
                                        <a href={link.URL} target="_blank" rel="noreferrer">
                                            {link.Title}
                                        </a>
                                        {link.Location && <span>{link.Location}</span>}
                                        {link.Contact && <span>{link.Contact}</span>}
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </section>
            );
        }

        function LinksControl() {
            const [links, setLinks] = useState([]);
            const [openCategory, setOpenCategory] = useState(null);
            const [error, setError] = useState(false);

            useEffect(() => {
                fetch('{{ asset('links.json') }}')
                    .then(response => response.ok ? response.json() : Promise.reject())
                    .then(data => setLinks(Array.isArray(data) ? data : []))
                    .catch(() => setError(true));
            }, []);

            if (error) {
                return <p role="alert">Unable to load shooting links.</p>;
            }

            return (
                <div id="links-accordion">
                    {categories.map(category => (
                        <LinkPanel
                            key={category.key}
                            category={category}
                            links={links}
                            open={openCategory === category.key}
                            onToggle={() => setOpenCategory(openCategory === category.key ? null : category.key)}
                        />
                    ))}
                </div>
            );
        }

        const linksRoot = document.getElementById('links-root');
        if (linksRoot) ReactDOM.createRoot(linksRoot).render(<LinksControl />);
    </script>
@endsection
