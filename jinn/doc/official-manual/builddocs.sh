rm JiNN-User-Manual.pdf
rm JiNN-User-Manual.html
rm JiNN-User-Manual.ps
rm *~
lyx --export pdf JiNN-User-Manual.lyx
lyx --export html JiNN-User-Manual.lyx
lyx --export ps JiNN-User-Manual.lyx
scp -r JiNN* pim@lingewoud.nl:/home/guide/project-jinn.org/htdomains/docs/
