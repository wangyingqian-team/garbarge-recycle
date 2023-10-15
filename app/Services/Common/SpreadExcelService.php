<?php
namespace App\Services\Common;

use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SpreadExcelService
{
    protected $excel;

    protected $title;

    protected $cells;

    protected $format = 'Xlsx';

    public function __construct()
    {
        $this->excel = new Spreadsheet();
    }

    public function setFormat($format)
    {
        $this->format = ucfirst($format);

        return $this;
    }

    /**
     * 设置导出名称
     *
     * @param $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * 设置单元格名称
     *
     * @param $cells
     *
     * @return $this
     */
    public function setCell(array $cells)
    {
        $this->cells = $cells;

        return $this;
    }

    /**
     * 导出
     *
     * @param $data
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     *
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function export($data)
    {
        $sheet = $this->excel->getActiveSheet();
        $index =  ord('A');
        $count = count($data);
        $i = 0;
        foreach ($this->cells as $k => $v){
            $chr = chr($index+$i);
            $sheet->getStyle($chr)->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setWrapText(true); //设置自动换行
            $sheet->getColumnDimension($chr)->setWidth(28);
            $sheet->setCellValue($chr.'1', $v);
            for ($j =0; $j <$count; $j ++){
                $value = $data[$j] ? (is_array($data[$j]) ? Arr::dot($data[$j]): $data[$j]) : '';
                $sheet->setCellValue($chr.($j+2), $value[$k] ?? '');
            }

            $i++;
        }

        $this->downloadExcel();
    }

    /**
     * 下载
     *
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    protected function downloadExcel()
    {
        if ($this->format == 'Xlsx') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        } else{
            header('Content-Type: application/vnd.ms-excel');
        }

        header("Content-Disposition: attachment;filename="
            . $this->title . date('Y-m-d') . '.' . strtolower($this->format));
        header('Cache-Control: max-age=0');
        $objWriter = IOFactory::createWriter($this->excel, $this->format);

        $objWriter->save('php://output');

        exit;
    }
}
