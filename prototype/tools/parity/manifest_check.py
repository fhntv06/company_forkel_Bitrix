"""Проверка манифеста принятых расхождений: у каждого файла есть причина и источник; хэши в reasons.json равны хэшам в v4-accepted-divergence.json и текущим файлам V4 (иначе — незадокументированная правка). python3 -I manifest_check.py"""
import json,os,hashlib,sys
H=os.path.dirname(os.path.abspath(__file__)); V4=os.path.join(H,'..','..','homepage-v4')
acc=json.load(open(os.path.join(H,'baseline','v4-accepted-divergence.json'),encoding='utf-8'))
rs=json.load(open(os.path.join(H,'baseline','v4-accepted-divergence.reasons.json'),encoding='utf-8'))['files']
bad=[]
for k,h in acc.items():
    r=rs.get(k)
    if not r or not r.get('reason') or not r.get('source'): bad.append(k+': нет причины/источника'); continue
    if r['sha256']!=h: bad.append(k+': хэш в reasons не равен манифесту')
    if hashlib.sha256(open(os.path.join(V4,k),'rb').read()).hexdigest()!=h: bad.append(k+': файл V4 изменён после фиксации хэша')
for k in rs:
    if k not in acc: bad.append(k+': есть в reasons, нет в манифесте')
print('\n'.join('FAIL '+b for b in bad) or 'OK манифест: %d файлов, причины и хэши согласованы'%len(acc)); sys.exit(1 if bad else 0)
