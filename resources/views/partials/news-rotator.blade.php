<div id="news-rotator-root" class="mb-4"></div>

<script src="https://unpkg.com/react@18/umd/react.development.js"></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
<script src="https://unpkg.com/babel-standalone@6.26.0/babel.min.js"></script>

<script type="text/babel">
const { useState, useEffect } = React;

function NewsRotator(){
  const [items, setItems] = useState([]);
  const [index, setIndex] = useState(0);

  useEffect(()=>{
    let mounted = true;
    fetch('/news/list')
      .then(r=> r.ok ? r.json() : [])
      .then(data => { if(mounted) setItems(Array.isArray(data) ? data : []); })
      .catch(()=>{});
    return ()=> mounted = false;
  },[]);

  useEffect(()=>{
    if(items.length === 0) return;
    const id = setInterval(()=>{
      setIndex(i => (i + 2) % items.length);
    }, 4000);
    return ()=> clearInterval(id);
  },[items]);

  function handleClick(item, idx){
    const param = item && (item.id || item.slug)
      ? ('?id=' + encodeURIComponent(item.id || item.slug))
      : ('?index=' + idx);
    window.location.href = '/web/news.php' + param;
  }

  const visible = [];
  if(items.length > 0){
    visible.push(items[index]);
    if(items.length > 1) visible.push(items[(index + 1) % items.length]);
  }

  return (
    <div className="row">
      <div className="col-12">
        <div className="list-group">
          {visible.length === 0 && (
            <div className="list-group-item">No news available.</div>
          )}
          {visible.map((it, i) => (
            <button
              key={i}
              type="button"
              className="list-group-item list-group-item-action"
              onClick={()=> handleClick(it, (index + i) % items.length)}
            >
              {it && (it.header || it.title || 'Untitled')}
            </button>
          ))}
        </div>
      </div>
    </div>
  );
}

const container = document.getElementById('news-rotator-root');
if(container){
  ReactDOM.createRoot(container).render(<NewsRotator />);
}
</script>
