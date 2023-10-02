<?php



$pmType = [
  'pt51' => [
    'value' => "pt51",
    'interpretation' => 'POH-AFM',
    'page' => [
      'orientation' => 'P',
      'unit' => 'mm',
      'format' => 'A5',
      'margins' => [ 'L' => 20, 'T' => 25, 'R' => 10, 'B' => 20 ],
      'headerMargin' => 5,
    ],
    'fontsize' => [
      'levelledPara' => [
        'title' =>[ '0' => 14, '1'  => 12, '2'  => 11, '3'  => 10, '4'  => '9'],
        'para' => 9,
        'figure' => [ 
          'title' => 9, 
          'legend' => [
            'header' => 8,
            'list' => 7
          ]
        ],
      ],
    ],
    'content' => [
      'indentation' => [
        'levelledPara' => [0,3,5,7,9]
      ],
      'header' => '/template/pt51_header.php',
      'footer' => '/template/pt51_footer.php'
    ]
  ],
];

$pmEntryType = [
  'pmt01' => [
    'interpretation' =>  'TP'
  ],
  'pmt61' => [
    'interpretation' => 'INTRODUCTION'
  ],
];

return [
  'pmType' => $pmType,
  'pmEntryType' => $pmEntryType,
];