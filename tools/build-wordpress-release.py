#!/usr/bin/env python3
"""Build the WordPress.org language-pack edition from the verified GitHub ZIP."""
from pathlib import Path, PurePosixPath
import hashlib,json,re,sys,zipfile
source, output = map(lambda p:Path(p).resolve(),sys.argv[1:])
slug='boltutil-payments-for-woocommerce'
output.mkdir(parents=True,exist_ok=True)
trunk=output/'trunk'
assert not trunk.exists(), 'Use a fresh output directory'
trunk.mkdir()
files=[]
with zipfile.ZipFile(source) as archive:
 for entry in archive.infolist():
  if entry.is_dir(): continue
  p=PurePosixPath(entry.filename)
  assert p.parts[0]==slug and '..' not in p.parts and not p.is_absolute()
  relative=PurePosixPath(*p.parts[1:])
  if relative.parts[0]=='languages': continue
  data=archive.read(entry)
  if str(relative)==slug+'.php':
   text=data.decode().replace(' * Domain Path: /languages\n','').replace("add_action( 'init', 'boltutil_wc_load_translations' );\n",'')
   text,n=re.subn(r'function boltutil_wc_load_translations\(\) \{.*?\n\}\n\n','',text,flags=re.S)
   assert n==1
   data=text.encode()
  if str(relative)=='readme.txt':
   # Preserve the directory's search terms, feature overview and demo disclosure.
   intro=Path(__file__).resolve().parents[1]/'wordpress-org-assets/directory-introduction.txt'
   text=data.decode()
   text,n=re.subn(r'(?<=\n== Description ==\n\n).*?(?=\nUSDT supports TRON)',lambda _:intro.read_text().rstrip()+'\n',text,count=1,flags=re.S)
   assert n==1, 'Directory description insertion point changed'
   text=re.sub(r'^Tags: .*$', 'Tags: woocommerce, usdt, usdc, crypto payments, payment gateway',text,count=1,flags=re.M)
   data=text.encode()
  target=trunk/str(relative);target.parent.mkdir(parents=True,exist_ok=True);target.write_bytes(data)
  files.append({'file':str(relative),'githubSha256':hashlib.sha256(archive.read(entry)).hexdigest(),'directorySha256':hashlib.sha256(data).hexdigest()})
version=re.search(r'^Stable tag: (.+)$',(trunk/'readme.txt').read_text(),re.M).group(1)
zipout=output/(slug+'.'+version+'.zip')
with zipfile.ZipFile(zipout,'w',zipfile.ZIP_DEFLATED) as archive:
 for p in sorted(trunk.rglob('*')):
  if p.is_file():
   entry=zipfile.ZipInfo(slug+'/'+p.relative_to(trunk).as_posix(),(2000,1,1,0,0,0));entry.compress_type=zipfile.ZIP_DEFLATED;entry.external_attr=0o100644<<16
   archive.writestr(entry,p.read_bytes())
differences=[row['file'] for row in files if row['githubSha256']!=row['directorySha256']]
assert set(differences)<= {slug+'.php','readme.txt'}
manifest={'version':version,'sourceZip':source.name,'sourceSha256':hashlib.sha256(source.read_bytes()).hexdigest(),'directoryZip':zipout.name,'directorySha256':hashlib.sha256(zipout.read_bytes()).hexdigest(),'editionDifferences':differences,'files':files}
(output/'manifest.json').write_text(json.dumps(manifest,indent=2)+'\n')
print(json.dumps({k:v for k,v in manifest.items() if k!='files'},indent=2))
