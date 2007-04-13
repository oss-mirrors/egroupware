#!/bin/sh

#official-manual
rm official-manual/JiNN-User-Manual.pdf
rm official-manual/JiNN-User-Manual.html
rm official-manual/*~
lyx --export pdf official-manual/JiNN-User-Manual.lyx
lyx --export html official-manual/JiNN-User-Manual.lyx

#tutorial-faq-app
rm tutorial-faq-app/tutorial-faq-app.pdf
rm tutorial-faq-app/tutorial-faq-app.html
rm tutorial-faq-app/*~
lyx --export pdf tutorial-faq-app/tutorial-faq-app.lyx
lyx --export html tutorial-faq-app/tutorial-faq-app.lyx

#BEAM ME UP SCOTTY
scp -r . pim@lingewoud.nl:/home/guide/project-jinn.org/htdomains/docs/
