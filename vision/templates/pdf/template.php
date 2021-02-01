<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

define('PDF_HEADER_LOGO', $arResult["HEADER_LOGO"]);
define('PDF_HEADER_LOGO_WIDTH', 55);
define('PDF_PAGE_WIDTH', 182);
define('PDF_FONT_SIZE_HEADER', 16);
define('PDF_FONT_SIZE_TITLE', 18);
define('PDF_FONT_SIZE_MAIN', 9);
define('PDF_MARGIN_LEFT', 14);
define('PDF_MARGIN_RIGHT', 14);

require_once($_SERVER['DOCUMENT_ROOT'].$componentPath.'/tcpdf/tcpdf.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$str_width = $pdf->GetStringWidth(PDF_HEADER_STRING);
if($str_width > 125) {
	define('PDF_MARGIN_TOP_TEMPLATE', 33);
} else {
	define('PDF_MARGIN_TOP_TEMPLATE', 27);
}

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor(PDF_AUTHOR);
// $pdf->SetTitle($arResult["NAME"]);

// $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, '', ''/*,PDF_HEADER_STRING*/);
// $pdf->setFooterData(array(0,64,0), array(0,64,128));
// $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP_TEMPLATE, PDF_MARGIN_RIGHT);
$pdf->SetMargins(PDF_MARGIN_LEFT, 14, PDF_MARGIN_RIGHT);
// $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
// $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
// $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
// $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('','',PDF_FONT_SIZE_MAIN);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
// $pdf->SetFont('','',20);

$pdf->AddPage();

$pdf->SetAutoPageBreak(true, 30);

$border = array(
	'LRTB' => array(
		'width' => .5,
		'cap' => 'square',
		'join' => 'miter',
		'dash' => 0,
		'color' => array(238,241,243)
	),
);

$pdf->Image(
	$_SERVER["DOCUMENT_ROOT"].$templateFolder.'/logo_pdf.png',// file
	'',// x
	'',// y
	PDF_PAGE_WIDTH,//width
	'',//height
	'',//type
	'',//link
	'',//align
	false//resize
	// '',//dpi
	// '',//palign
	// false,//ismask
	// false,//imgmask
	// $border,//border
	// false,//fitbox
	// false,//hidden
	// false//fitonpage
	//false,//alt
	//array()//altimgs
);

$pdf->setY(40);
// $pdf->setX(0);

$pdf->Ln();
$pdf->SetFont('','B',16);
$pdf->Write('', 'WATTS VISION configurator');
$pdf->SetFont('','',PDF_FONT_SIZE_MAIN);
$pdf->Ln();
$pdf->Ln();
$pdf->Write('', 'The following components must be ordered based on the chosen configuration and on the best comfort. For further information, you can contact your sales representative or technical support');
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
// $nameWidtd = PDF_PAGE_WIDTH - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT - 100 - 100;
$table = '<table cellpadding="3"><thead><tr>';
// $table .= '<th width="100" style="border-bottom: .1px solid #3c3c3c; background-color: #dcdcdc;"><b>Article number</b></th>';
$table .= '<th width="100" style="border-bottom: .1px solid #3c3c3c;"><b>Article</b></th>';
$table .= '<th width="315" style="border-bottom: .1px solid #3c3c3c;"><b>Description</b></th>';
// $table .= '<th width="100" style="border-bottom: .1px solid #3c3c3c;"><b>Number</b></th></tr>';
$table .= '<th width="100" style="border-bottom: .1px solid #3c3c3c;"><b>Qt.</b></th></tr>';
$table .= '</thead><tbody>';
if (is_array($arResult['ITEMS'])) {
	foreach ($arResult['ITEMS'] as $key => $arItem) {
		$table .= '<tr>';
		// $table .= '<td width="100" style="border-bottom: .1px solid #3c3c3c; background-color: #dcdcdc;">'.$arItem['ARTICLE'].'</td>';
		$table .= '<td width="100" style="border-bottom: .1px solid #3c3c3c;">'.$arItem['ARTICLE'].'</td>';
		$table .= '<td width="315" style="border-bottom: .1px solid #3c3c3c;">'.$arItem['TEXT'].'</td>';
		$table .= '<td width="100" style="border-bottom: .1px solid #3c3c3c;">'.$arItem['QUANTITY'].'</td>';
		$table .= '</tr>';
	}
}
$table .= '</tbody></table>';

$pdf->writeHTML($table);

// file_put_contents(__DIR__.'/info-'.date('Ymd').'.log', '');
// file_put_contents(__DIR__.'/info-'.date('Ymd').'.log', '--- ['.date('d.m.Y H:i:s').'] '.__FILE__.':'.__LINE__.' ---'.PHP_EOL.'+ PDF_PAGE_WIDTH = '.PDF_PAGE_WIDTH.PHP_EOL, FILE_APPEND | LOCK_EX);
// file_put_contents(__DIR__.'/info-'.date('Ymd').'.log', '--- ['.date('d.m.Y H:i:s').'] '.__FILE__.':'.__LINE__.' ---'.PHP_EOL.'+ PDF_MARGIN_LEFT = '.PDF_MARGIN_LEFT.PHP_EOL, FILE_APPEND | LOCK_EX);
// file_put_contents(__DIR__.'/info-'.date('Ymd').'.log', '--- ['.date('d.m.Y H:i:s').'] '.__FILE__.':'.__LINE__.' ---'.PHP_EOL.'+ PDF_MARGIN_RIGHT = '.PDF_MARGIN_RIGHT.PHP_EOL, FILE_APPEND | LOCK_EX);
// file_put_contents(__DIR__.'/info-'.date('Ymd').'.log', '--- ['.date('d.m.Y H:i:s').'] '.__FILE__.':'.__LINE__.' ---'.PHP_EOL.'+ test'.PHP_EOL, FILE_APPEND | LOCK_EX);
// file_put_contents(__DIR__.'/info-'.date('Ymd').'.log', '--- ['.date('d.m.Y H:i:s').'] '.__FILE__.':'.__LINE__.' ---'.PHP_EOL.'+ $arResult:'.PHP_EOL.print_r($arResult, true).PHP_EOL, FILE_APPEND);

$bSend = false;
$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
if ($request->getPost('action') == 'send_email') {
	$outputType = 'F';
	$bSend = true;
} else {
	// $outputType = 'I';
	$outputType = 'F';
}

// $pdf->Output(__DIR__.'/vision_results.pdf', 'I');
// $fileName = sys_get_temp_dir();
// $fileName .= '/vision_results-'.rand().'.pdf';
// $fileName = $_SERVER['DOCUMENT_ROOT'];

// preg_match("/^.*\/$/", $fileName, $matches);

// if (!empty($matches)) {
// 	$fileName = substr($fileName, 0, -1);
// }

// $fileName .= '/upload/vision/vision_results-'.rand().'.pdf';

$documentRoot = $_SERVER['DOCUMENT_ROOT'];
preg_match("/^.*\/$/", $documentRoot, $matches);
if (!empty($matches)) {
	$documentRoot = substr($documentRoot, 0, -1);
}
$folder = '/upload/vision';
$fileName = '/vision_results-'.rand().'.pdf';

$pdf->Output($documentRoot.$folder.$fileName, $outputType);



if ($fileName) {
	// $pdf->Output($fileName, $outputType);
	// $pdf->Output($arResult['PDF_FILE_NAME'], $outputType);

	if ($bSend) {
		$arRequest = $request->toArray();
		$userEmail = $arRequest['email'];
		$arEventFields = array(
			'TEXT' => 'Bedankt voor het gebruiken van de Watts Vision configuratietool. In de bijlage vindt u een overzicht van de benodigde artikelen en aantallen.<br><br>Voor vragen kunt u terecht bij uw groothandel [Choose].<br><br>Met vriendelijke groet,<br>Watts Water Technologies Benelux',
			'EMAIL_TO' => $userEmail,
		);

		\Bitrix\Main\Mail\Event::send([    
			'EVENT_NAME' => 'VISION_RESULT',
			// 'MESSAGE_ID' => 64,
			'LID' => SITE_ID,
			'C_FIELDS' => $arEventFields,
			'FILE' => [
				// $fileName
				$documentRoot.$folder.$fileName,
			]
		]);

		echo CUtil::PhpToJsObject(['success' => 'Y', 'message' => 'The result has been sent to '.$userEmail], true, true);
	} else {
		echo CUtil::PhpToJsObject(['success' => 'Y', 'file' => $folder.$fileName], true, true);
	}
}

exit;