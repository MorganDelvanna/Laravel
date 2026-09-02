<div id="news-rotator-root" class="mb-4"></div>

<script src="https://unpkg.com/react@18/umd/react.development.js"></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
<script src="https://unpkg.com/babel-standalone@6.26.0/babel.min.js"></script>

<script type="text/babel">
const { useState, useEffect } = React;

function NewsViewer(){
  const [items, setItems] = useState([]);

    useEffect(()=>{
    let mounted = true;
    fetch('/news/list')
      .then(r=> r.ok ? r.json() : [])
      .then(data => { if(mounted) setItems(Array.isArray(data) ? data : []); })
      .catch(()=>{});
    return ()=> mounted = false;
  },[]);

  return (
        <div className="list-group">
          {items.length === 0 && (
            <div className="list-group-item">No news available.</div>
          )}
          {items.map((it, i) => (
            <article key={it.id || i}>
              <div className={`row ${it.id % 2 === 0 ? 'alt' : ''}`}>
                <div className="col-12">
                  <h2>{it && (it.header || it.title || 'Untitled')}</h2>
                </div>
                </div>
              <div className={`row ${it.id % 2 === 0 ? 'alt' : ''}`}>
                <div className="col-12">
                  {it && (it.description || '')}
                </div>
                </div>
            </article>
          ))}
        </div>
  );
}

      const newsRoot = document.getElementById('news-rotator-root');
      if(newsRoot){
        ReactDOM.createRoot(newsRoot).render(<NewsViewer />);
      }
      </script>