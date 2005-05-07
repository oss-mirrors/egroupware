<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");

// Some data
$datay = array(28,19,18,23,12,11);
$data2y = array(14,18,33,29,39,55);

// A nice graph with anti-aliasing
$graph = new Graph(400,200,"auto");
$graph->img->SetMargin(40,180,40,40);	
$graph->SetBackgroundImage("tiger_bkg.png",1);

// Adjust brightness and contrast for background image
// must be between -1 <= x <= 1, (0,0)=original image
$graph->AdjBackgroundImage(0,0);

$graph->img->SetAntiAliasing("white");
$graph->SetScale("textlin");
$graph->SetShadow();
$graph->title->Set("Background image");

// Use built in font
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Slightly adjust the legend from it's default position in the
// top right corner. 
$graph->legend->Pos(0.05,0.5,"right","center");

// Create the first line
$p1 = new LinePlot($datay);
$p1->mark->SetType(MARK_FILLEDCIRCLE);
$p1->mark->SetFillColor("red");
$p1->mark->SetWidth(4);
$p1->SetColor("blue");
$p1->SetCenter();
$p1->SetLegend("Triumph Tiger -98");
$graph->Add($p1);

// ... and the second
$p2 = new LinePlot($data2y);
$p2->mark->SetType(MARK_STAR);
$p2->mark->SetFillColor("red");
$p2->mark->SetWidth(4);
$p2->SetColor("red");
$p2->SetCenter();
$p2->SetLegend("New tiger -99");
$graph->Add($p2);

// Output line
$graph->Stroke();

?>


