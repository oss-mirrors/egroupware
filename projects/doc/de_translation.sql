# First we will delete all entries for projects, to prevent dups when updating.                                                           
DELETE from lang WHERE app_name='projects';
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Projects','projects','de','Projekte');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Projects','common','de','Projekte');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Return to projects','projects','de','Zur&uuml;ck zu Projekte');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Project billing','common','de','Projekt Abrechnung');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'All open hours','projecthours','de','Alle offenen Stunden');     
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'All done hours','projecthours','de','Alle erledigten Stunden');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Projects','admin','de','Projekte');                                                                
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Activity','projects','de','Abrechnungskategorie');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Active','projects','de','Aktiv');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'New project','projects','de','Neues Projekt');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Address book','projects','de','Adress Buch');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'My address','projects','de','Meine Adresse');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Firstname','projects','de','Vorname');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Lastname','projects','de','Nachname');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Username','projects','de','Benutzername');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Add hour','projects','de','Job hinzuf&uuml;gen');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Edit','projects','de','Bearbeiten');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Activities list','projects','de','Kategorienliste');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Activity ID','projects','de','Kategorie ID');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Add activity','projects','de','Abrechnungskategorie hinzuf&uuml;gen');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Edit activity','projects','de','Abrechnungskategorie bearbeiten');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Coordinator','projects','de','Koordinator');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Project name','projects','de','Projektname');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Project ID','projects','de','Projekt ID');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Add project','projects','de','Projekt hinzuf&uuml;gen');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Update project','projects','de','Projekt aktualisieren');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Status','projects','de','Status');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Title','projects','de','Titel');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'User statistics','projects','de','Mitarbeiterstatistiken');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Date due','projects','de','F&auml;llig');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Project statistics','projects','de','Projektstatistiken');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Admin','projects','de','Admin');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Project hours','projects','de','Projektstunden');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'View hours','projects','de','Jobs anzeigen');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Copy template','projects','de','Vorlage kopieren');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Search','projects','de','Suche');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Submit','projects','de','Abschicken');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Date','projects','de','Datum');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Project billing','projects','de','Projektabrechnung');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Budget','projects','de','Budget');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Customer','projects','de','Kunde');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Bookable activities','projects','de','Nicht-Abrechenbare Kategorien');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Billable activities','projects','de','Abrechenbare Kategorien');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Bill per workunit','projects','de','Preis der Arbeitseinheit');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Minutes per workunit','projects','de','Minuten pro Arbeitseinheit');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Project list','projects','de','Projektliste');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Edit project','projects','de','Projekt bearbeiten');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Add project','projects','de','Projekt hinzuf&uuml;gen');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Add project hours','projects','de','Projektstunden hinzuf&uuml;gen');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Status','projects','de','Status');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Entry date','projects','de','Eintragsdatum');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Nonactive','projects','de','Nicht-Aktiv');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Description','projects','de','Beschreibung');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Template','projects','de','Vorlage');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Remark','projects','de','Job [ Beschreibung ]');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Remark required','projects','de','Beschreibung des Jobs erforderlich?');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Time','projects','de','Zeit');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Archiv','projects','de','Archiv');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Done','projects','de','Erledigt');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Open','projects','de','Offen');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Billed','projects','de','Abgerechnet');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Employee','projects','de','Mitarbeiter');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Edit hours','projects','de','Jobs bearbeiten');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Delete hours','projects','de','Stunden l&ouml;schen');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Calculate','projects','de','Berechnen');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Statistic','projects','de','Statistiken');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Start date','projects','de','Startdatum');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'There are no entries','projects','de','Es existieren keine Eintr&auml;ge!');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'You have selected an invalid activity','projects','de','Sie haben eine ung&uuml;ltige Abrechnungskategorie ausgew&auml;hlt!');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'You have selected an invalid date','projects','de','Sie haben ein ung&uuml;ltiges Datum ausgew&auml;hlt!');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'You have to enter a remark','projects','de','Bitte geben Sie eine Bemerkung ein!');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Are you sure you want to delete this entry','projects','de','Sind Sie sicher, da&szlig; Sie diesen Eintrag l&ouml;schen wollen?');
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Projects preferences','projects','de','ProjeKt Einstellungen');                                                                  
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Projects','preferences','de','Projekte');                                                                                        
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Return to projects','projects','de','Zur&uuml;ck zu Projekte');                                                                  
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Invoice','projects','de','Rechnung');  
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Invoice ID','projects','de','Rechnungs ID');                                                                                            
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Invoice list','projects','de','Rechnung Liste');                                                                                  
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'All invoices','projects','de','Alle Rechnungen');                                                                                
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Invoice date','projects','de','Rechnung Datum');                                                                                 
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Create invoice','projects','de','Erstelle Rechnung');                                                                             
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Update','projects','de','Aktualisieren');                                                                                        
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Activity','projects','de','Kategorie');                                                                                          
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Active','projects','de','Aktiv');                                                                                                
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Select','projects','de','Ausw&auml;hlen');                                                                                       
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Workunits','projects','de','Arbeitseinheiten');                                                                                        
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Netto','projects','de','Netto');                                                                                                 
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Sum','projects','de','Summe');                                                                                                   
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Edit','projects','de','Bearbeiten');                                                                                             
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Add project','projects','de','Projekt hinzuf&uuml;gen');                                                                         
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Update project','projects','de','Projekt aktualisieren');                                                                        
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Status','projects','de','Status');                                                                                               
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Project hours','projects','de','Projekt Stunden');                                                                                
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Billed','projects','de','Abgerechnet');                                                                                          
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Employee','projects','de','Mitarbeiter');                                                                                        
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Delivery','projects','de','Lieferung'); 
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Delivery ID','projects','de','Lieferungs ID');                                                                                        
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Delivery note','projects','de','Lieferschein');                                                                                
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'All delivery notes','projects','de','Alle Lieferscheine');                                                                     
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Delivery list','projects','de','Lieferungs-Liste');                                                                             
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'All deliverys','projects','de','Alle Lieferungen');                                                                            
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Delivery date','projects','de','Lieferungs Datum');                                                                            
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Create delivery','projects','de','Lieferung erstellen');                                                                        
REPLACE INTO lang (message_id, app_name, lang, content) VALUES( 'Print delivery','projects','de','Lieferung drucken');