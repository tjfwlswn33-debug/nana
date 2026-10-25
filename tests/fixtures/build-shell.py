"""Build a local fake engine around the actual optional integration files."""
import pathlib
import shutil
import sys

output = pathlib.Path(sys.argv[1]).resolve()
source = pathlib.Path(__file__).resolve().parents[2]
fixtures = pathlib.Path(sys.argv[2]).resolve()
site = output / 'site'
(site / 'bbs').mkdir(parents=True, exist_ok=True)
(site / 'custommenu').mkdir(exist_ok=True)
(site / 'css').mkdir(exist_ok=True)
for file in (source / 'integration').glob('*.php'):
    shutil.copyfile(file, site / file.name)
shutil.copytree(fixtures / 'skin', site / 'skin', dirs_exist_ok=True)
for file in fixtures.glob('*.html'):
    shutil.copyfile(file, output / file.name)
for directory in ['skin', 'data']:
    shutil.copytree(fixtures / directory, output / directory, dirs_exist_ok=True)
(site / '_common.php').write_text('''<?php
if (!defined('_GNUBOARD_')) {
 define('_GNUBOARD_', true); define('G5_PATH', __DIR__);
 define('G5_URL', '/site'); define('G5_BBS_URL','/site/bbs'); define('G5_CSS_URL','/site/css'); define('G5_IS_MOBILE',true);
 $config=['cf_bgm'=>1];
 function get_main_link(){return '/site/main.php';}
 function add_stylesheet($html,$priority){echo $html;}
 function get_site_content($key){return '<a href="/site/bbs/board.php?bo_table=oc" target="frm_main">Archive</a>';}
}
''')
(site / 'head.sub.php').write_text('<!doctype html><html lang="ko"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head><body>')
(site / 'tail.sub.php').write_text('</body></html>')
(site / 'head.php').write_text('<?php include __DIR__."/head.sub.php"; ?><div id="original-head">Original theme header</div>')
(site / 'tail.php').write_text('<div id="original-tail">Original theme footer</div><?php include __DIR__."/tail.sub.php"; ?>')
(site / 'custommenu/index.php').write_text('<nav id="original-menu"><a target="_top" href="/site/bbs/board.php?bo_table=oc">Archive</a></nav>')
(site / 'css/index.css').write_text('body{margin:0;background:#e9edf0}#main{display:block;width:100%;height:100vh;border:0}#original-menu{position:fixed;right:20px;top:10px;z-index:10;background:white;padding:8px}#site_bgm_box{position:fixed;bottom:10px;left:10px;z-index:10}#bgm_frame{width:120px;height:45px;border:0}')
(site / 'bgm.php').write_text('<script>parent.__bgmLoads=(parent.__bgmLoads||0)+1;</script><button id="player-control" type="button">BGM controls</button>')
(site / 'bbs/board.php').write_text('''<?php
$file=isset($_GET['wr_id']) ? ((int)$_GET['wr_id']>=11 ? ((isset($_GET['nana_section']) && $_GET['nana_section']==='timeline') ? 'event-timeline.html':'event.html'):'profile.html') : ((isset($_GET['nana_section']) && $_GET['nana_section']==='timeline') ? 'timeline.html':'list.html');
readfile(dirname(__DIR__,2).'/'.$file);
''')
(site / 'bbs/write.php').write_text('''<?php
$file=(isset($_GET['w']) && $_GET['w']==='u' && (int)($_GET['wr_id']??0)>=11) ? 'edit-event.html' : ((isset($_GET['nana_type']) && $_GET['nana_type']==='timeline') ? 'write-event.html':'write.html');
readfile(dirname(__DIR__,2).'/'.$file);
''')
print('Built shell integration fixture')
