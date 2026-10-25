"""Serve static skin fixtures; no real board engine or database is involved."""
import argparse
import http.server
import pathlib
import urllib.parse

parser = argparse.ArgumentParser()
parser.add_argument('directory')
parser.add_argument('--port', type=int, default=8081)
args = parser.parse_args()
root = pathlib.Path(args.directory).resolve()

class Handler(http.server.SimpleHTTPRequestHandler):
    def __init__(self, *a, **kw):
        super().__init__(*a, directory=str(root), **kw)

    def do_GET(self):
        url = urllib.parse.urlsplit(self.path)
        query = urllib.parse.parse_qs(url.query)
        if url.path == '/site/index.php':
            body = b'<!doctype html><html><head><meta name="viewport" content="width=device-width,initial-scale=1"></head><body style="margin:0"><div id="frame-menu">Personal home</div><div id="frame-bgm" data-state="playing">BGM controls</div><iframe name="frm_main" title="Personal archive" src="/site/bbs/board.php?bo_table=oc" style="width:100%;height:95vh;border:0"></iframe></body></html>'
            self.send_response(200)
            self.send_header('Content-Type', 'text/html; charset=utf-8')
            self.end_headers()
            self.wfile.write(body)
            return
        if url.path == '/site/main.php':
            self.path = '/list.html'
        elif url.path == '/site/bbs/board.php':
            self.path = (('/event-timeline.html' if query.get('nana_section') == ['timeline'] else '/event.html') if int(query['wr_id'][0]) >= 11 else '/profile.html') if 'wr_id' in query else ('/timeline.html' if query.get('nana_section') == ['timeline'] else '/list.html')
        elif url.path == '/site/bbs/write.php':
            self.path = '/edit-event.html' if query.get('w') == ['u'] and int(query.get('wr_id', ['0'])[0]) >= 11 else ('/write-event.html' if query.get('nana_type') == ['timeline'] else '/write.html')
        super().do_GET()

    def log_message(self, *_):
        pass

http.server.ThreadingHTTPServer(('127.0.0.1', args.port), Handler).serve_forever()
