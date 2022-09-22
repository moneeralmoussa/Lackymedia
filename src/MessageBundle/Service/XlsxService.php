<?php

namespace AppBundle\Service;
use Carbon\Carbon;

class XlsxService
{
	public function createXlsx($data, $filename)
    {
		/* Example
		$data = array(
		    array(
		        'PHP7',
		        'Java Script',
		        'Java',
		        'Node JS',
		    )
		);

		$filename = 'restgehaltsanspruch_'.Carbon::now()->format('Y-m-d').'.xls';
		*/

		$doc = new \PHPExcel();
		$doc->setActiveSheetIndex(0);
		$doc->getActiveSheet()->fromArray($data);

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$objWriter = \PHPExcel_IOFactory::createWriter($doc, 'Excel5');

		$objWriter->save('php://output');
		return $response;

	}

}
