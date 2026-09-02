<div id="menu-root"></div>

<script src="https://unpkg.com/react@18/umd/react.development.js"></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
<script src="https://unpkg.com/babel-standalone@6.26.0/babel.min.js"></script>

<script type="text/babel">
const { useEffect, useState } = React;

function MenuItem({ item, open, onToggle }) {
    const [localOpen, setLocalOpen] = useState(false);
    const hasChildren = Array.isArray(item.children) && item.children.length > 0;
    const isOpen = typeof open === 'boolean' ? open : localOpen;

    if (!hasChildren) {
        return <li className="nav-item"><a className="nav-link" href={item.href}>{item.text}</a></li>;
    }

    return (
        <li className="nav-item dropdown">
            <button
                className="nav-link dropdown-toggle"
                type="button"
                aria-expanded={isOpen}
                onClick={() => onToggle ? onToggle() : setLocalOpen(!localOpen)}
            >
                {item.text}
            </button>
            <ul className={'dropdown-menu' + (isOpen ? ' show' : '')}>
                {item.children.map((child, index) => <MenuItem key={index} item={child} />)}
            </ul>
        </li>
    );
}

function Menu() {
    const [items, setItems] = useState([]);
    const [error, setError] = useState(false);
    const [openDropdown, setOpenDropdown] = useState(null);

    useEffect(() => {
        fetch('{{ asset('menu.json') }}')
            .then(response => response.ok ? response.json() : Promise.reject())
            .then(data => setItems(Array.isArray(data) ? data : []))
            .catch(() => setError(true));
    }, []);

    return (
        <nav className="navbar navbar-expand-lg mb-4" aria-label="Main navigation">
            <div className="container">
                <button className="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu" aria-controls="mainMenu" aria-expanded="false" aria-label="Toggle navigation">
                    <span className="navbar-toggler-icon"></span>
                </button>
                <div className="collapse navbar-collapse" id="mainMenu">
                    <ul className="navbar-nav me-auto mb-2 mb-lg-0">
                        {error && <li className="nav-item"><span className="nav-link">Menu unavailable</span></li>}
                        {items.map((item, index) => (
                            <MenuItem
                                key={index}
                                item={item}
                                open={openDropdown === index}
                                onToggle={() => setOpenDropdown(openDropdown === index ? null : index)}
                            />
                        ))}
                    </ul>
                </div>
            </div>
        </nav>
    );
}

const menuRoot = document.getElementById('menu-root');
if (menuRoot) ReactDOM.createRoot(menuRoot).render(<Menu />);
</script>