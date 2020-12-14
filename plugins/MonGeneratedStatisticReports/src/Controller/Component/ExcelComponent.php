<?php
namespace MonGeneratedStatisticReports\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;

class ExcelComponent extends Component
{
    private $writer;
    private $template;
    private $tables = [];
    public $styleReplace = [
        'background-color' => 'fill',
        'font-family' => 'font',
        'text-align' => 'halign',
        'vertical-align' => 'valign'
    ];

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->writer = new \XLSXWriter();
        $this->template = new \DOMDocument();
    }

    public function setTemplate(string $template)
    {
        $this->template->loadHTML('<?xml encoding="utf-8" ?>' . $template);

        return $this;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function style(string $styles, ?array $baseStyles = [], ?bool $replace = true)
    {
        $result = [];
        if ($baseStyles) {
            foreach ($baseStyles as $baseStyleName => $baseStyle) {
                $result[trim($baseStyleName)] = trim($baseStyle);
            }
        }

        if ($styles) {
            $styles = explode(';', $styles);
            foreach ($styles as $key => $style) {
                if ($style) {
                    $styleElement = explode(':', trim($style));
                    $styleElementName = trim($styleElement[0]);
                    if ($replace && isset($this->styleReplace[$styleElementName])) {
                        $styleElementName = $this->styleReplace[$styleElementName];
                    }
                    $result[$styleElementName] = trim($styleElement[1]);
                }
            }
        }

        return $result;
    }

    public function content(\DOMNodeList $content)
    {
        $result = [];
        foreach ($content as $key => $item) {
            if ($item->hasChildNodes()) {
                $result += $this->content($item->childNodes);
                $result += $this->style($item->getAttribute('style'));
                switch ($item->nodeName) {
                    case 's':
                        $result += ['font-style' => 'strikethrough'];
                        break;
                    case 'u':
                        $result += ['font-style' => 'underline'];
                        break;
                    case 'em':
                        $result += ['font-style' => 'italic'];
                        break;
                    case 'strong':
                        $result += ['font-style' => 'bold'];
                        break;
                }
            }
        }

        return $result;
    }

    public function table(\DOMNodeList $tableList)
    {
        $count = 0;
        foreach ($tableList as $key => $table) {
            if ($table->hasChildNodes()) {
                foreach ($table->childNodes as $caption) {
                    if ($caption->nodeName === 'caption') {
                        $this->tables[$count]['caption'] = $caption->nodeValue;
                        break;
                    } else {
                        $this->tables[$count]['caption'] = 'Sheet_' . $key;
                    }
                }
                $thead = $this->thead($table->childNodes);
                $tbody = $this->tbody($table->childNodes);
                $thead ? $this->tables[$count]['thead'] = $thead : null;
                $tbody ? $this->tables[$count]['tbody'] = $tbody : null;
                $count++;
            }
        }

        return $this->tables;
    }

    public function thead(\DOMNodeList $tableChildNodes)
    {
        $result = [];
        $count = 0;
        foreach ($tableChildNodes as $key => $thead) {
            if ($thead->nodeName === 'thead' && $thead->hasChildNodes()) {
                $tr = $this->tr($thead->childNodes);
                $tr ? $result[$count]['tr'] = $tr : null;
                $count++;
            }
        }

        return $result;
    }

    public function tbody(\DOMNodeList $tableChildNodes)
    {
        $result = [];
        $count = 0;
        foreach ($tableChildNodes as $key => $tbody) {
            if ($tbody->nodeName === 'tbody' && $tbody->hasChildNodes()) {
                $tr = $this->tr($tbody->childNodes);
                $tr ? $result[$count]['tr'] = $tr : null;
                $count++;
            }
        }

        return $result;
    }

    public function tr(\DOMNodeList $tbodyChildNodes)
    {
        $result = [];
        $count = 0;
        foreach ($tbodyChildNodes as $key => $tr) {
            if ($tr->nodeName === 'tr' && $tr->hasChildNodes()) {
                $th = $this->th($tr->childNodes);
                $td = $this->td($tr->childNodes);
                $th ? $result[$count]['th'] = $th : null;
                $td ? $result[$count]['td'] = $td : null;
                $count++;
            }
        }

        return $result;
    }

    public function th(\DOMNodeList $trChildNodes)
    {
        $result = [];
        $count = 0;
        foreach ($trChildNodes as $key => $th) {
            if ($th->nodeName === 'th') {
                $styles = $this->style($th->getAttribute('style'), [
                    'border' => 'left,right,top,bottom',
                    'font-style' => 'bold',
                    'wrap_text' => true,
                ]);
                
                if ($th->hasChildNodes()) {
                    $styles += $this->content($th->childNodes);
                }

                $result[$count] = [
                    'style' => $styles,
                    'content' => preg_replace('/\s+/', ' ', $th->nodeValue),
                    'colspan' => $th->getAttribute('colspan'),
                    'rowspan' => $th->getAttribute('rowspan')
                ];

                $count++;
            }
        }

        return $result;
    }

    public function td(\DOMNodeList $trChildNodes)
    {
        $result = [];
        $count = 0;
        foreach ($trChildNodes as $key => $td) {
            if ($td->nodeName === 'td') {
                $styles = $this->style($td->getAttribute('style'), [
                    'border' => 'left,right,top,bottom',
                    'wrap_text' => true,
                ]);
                
                if ($td->hasChildNodes()) {
                    $styles += $this->content($td->childNodes);
                }

                $result[$count] = [
                    'style' => $styles,
                    'content' => preg_replace('/\s+/', ' ', $td->nodeValue),
                    'colspan' => $td->getAttribute('colspan'),
                    'rowspan' => $td->getAttribute('rowspan')
                ];

                $count++;
            }
        }

        return $result;
    }

    public function parseTables(bool $first = true, array $items = [], array $data = [], string $parentKeyName = '')
    {
        if ($first) {
            $this->writer->setAuthor(Configure::read('productName'));
            $template = $this->getTemplate();
            $items = $this->table($template->getElementsByTagName('table'));
            $data = [
                'caption' => [],
                'rows' => [],
                'contents' => [],
                'styles' => [],
                'cellspans' => []
            ];
        }

        foreach ($items as $key => $item) {
            if (is_array($item) && $item) {
                if (isset($item['caption']) && $item['caption']) {
                    $data = [
                        'caption' => $item['caption'],
                        'rows' => [],
                        'contents' => [],
                        'styles' => [],
                        'cellspans' => []
                    ];
                } else if (!$data['caption']) {
                    continue;
                }
                
                if (
                    isset($item['style']) && isset($item['content'])
                    && isset($item['colspan']) && isset($item['rowspan'])
                ) {
                    $rowsCount = count($data['rows']);
                    !$key || !isset($data['rowspanStartKey']) ? $data['rowspanStartKey'] = 0 : null;
                    
                    if ($rowsCount && isset($data['rows'][$rowsCount - 1])) {
                        $cellspans = $data['rows'][$rowsCount - 1]['cellspans'];
                        for ($i = $data['rowspanStartKey']; $i < count($cellspans); $i++) {
                            if ($cellspans[$i] && $cellspans[$i]['rowspan'] >= 2) {
                                if ($cellspans[$i]['colspan'] >= 2) {
                                    for ($j = 1; $j < $cellspans[$i]['colspan']; $j++) {
                                        $data['contents'][] = "";
                                        $data['styles'][] = [];
                                        $data['cellspans'][] = [
                                            'colspan' => $cellspans[$i]['colspan'],
                                            'rowspan' => $cellspans[$i]['rowspan'] - 1
                                        ];
                                    }
                                } else {
                                    $data['contents'][] = "";
                                    $data['styles'][] = [];
                                    $data['cellspans'][] = [
                                        'colspan' => 1,
                                        'rowspan' => $cellspans[$i]['rowspan'] - 1
                                    ];
                                }
                            }
                            
                            if (
                                !isset($cellspans[$i + 1]) || !$cellspans[$i + 1]
                                || $cellspans[$i + 1]['rowspan'] < 2
                            ) {
                                $data['rowspanStartKey'] = $i + 1;
                                break;
                            }
                        }
                    }
                    
                    $data['contents'][] = $item['content'];
                    $data['styles'][] = $item['style'];
                    $data['cellspans'][] = ['colspan' => $item['colspan'], 'rowspan' => $item['rowspan']];
                    
                    if ($item['colspan']) {
                        for ($i = 1; $i < $item['colspan']; $i++) {
                            $data['contents'][] = "";
                            $data['styles'][] = [];
                            $data['cellspans'][] = [
                                'colspan' => $item['colspan'] - 1,
                                'rowspan' => $item['rowspan']
                            ];
                        }
                    }
                    
                } else {
                    $data = $this->parseTables(false, $item, $data, $key);
                }
            }
            
            if ($parentKeyName === 'tr' && $data['contents']) {
                $data['rows'][$key] = $data;
                if (isset($data['rows'][$key]['rows'])) {
                    unset($data['rows'][$key]['rows']);
                }
                
                $data['contents'] = $data['styles'] = $data['cellspans'] = [];
                
                if ($key === count($items) - 1) {
                    foreach ($data['rows'] as $k => $v) {
                        $this->writer->writeSheetRow($data['caption'], $v['contents'], $v['styles']);
                        foreach ($v['cellspans'] as $kk => $vv) {
                            if (isset($vv['colspan']) && $vv['colspan']) {
                                $row = isset($vv['rowspan']) && $vv['rowspan'] ? $k + $vv['rowspan'] - 1 : $k;
                                $this->writer->markMergedCell($data['caption'], $k, $kk, $row, $kk + $vv['colspan'] - 1);
                            }
                        }
                    }
                }
            }
        }
        
        if ($first) {
            return $this->writer;
        } else {
            return $data;
        }
    }

    public function render()
    {
        return $this->parseTables()->writeToString();
    }
}
