<?php



$pmType = [
  '' => [
    'interpretation' => 'DEFAULT',
    'value' => '',
    'useheader' => false,
    'usefooter' => false,
    'usetoc' => false,
    'usebookmark' => false,
    'page' => [
      'orientation' => 'P',
      'unit' => 'mm',
      'format' => 'A4',
      'margins' => [ 'L' => 20, 'T' => 22, 'R' => 10, 'B' => 17 ],
      'headerMargin' => 5,
      'footerMargin' => 10,
    ],
    'fontsize' => [
      'levelledPara' => [
        'title' =>[12,11,10,9,8],
        'figure' => [ 
          'title' => 9, 
          'legend' => [
            'header' => 8,
            'list' => 7
          ]
        ],
      ],
      'para' => 9,
    ],
    'fontfamily' => 'tahoma',
    'content' => [
      'padding' => ['levelledPara' => [0,3,5,7,9]],
      'header' => 'pt51_header.php',
      'footer' => 'pt51_footer.php',
      'tablestyle' => [
        'loa' => [
          'cellpadding' => '0mm',
        ],
        'terminologies_notice' => [
          'cellpadding' => '1mm',
        ],
        'alltdcenter' => [
          'cellpadding' => '1mm',
        ],
        'engine_limitation' => [
          'cellpadding' => '1mm',
        ],
      ],
    ],
    'attributes' =>[
      'crewMemberType' => [
        'cm02' => 'CM2',
        'cm03' => 'CM3',
        'cm04' => 'CM4',
        'cm05' => 'CM5',
      ]
    ],
  ],
];

$pmEntryType = [
  '' => [
    'interpretation' =>  '',
    'useheader' => false,
    'usefooter' => false,
    'usetoc' => false,
    'usebookmark' => false,
    'printpageident' => false,
    'page' => [
      'margins' => [ 'L' => 20, 'T' => 10, 'R' => 10, 'B' => 15 ],
    ],
  ],
  'pmt01' => [
    'interpretation' =>  'TITLE PAGE',
    'useheader' => false,
    'usefooter' => false,
    'usetoc' => false,
    'usebookmark' => false,
    'printpageident' => false,
    'page' => [
      'margins' => [ 'L' => 20, 'T' => 10, 'R' => 10, 'B' => 15 ],
    ],
  ],
  'pmt06' => [
    'interpretation' =>  'LEODM',
    'useheader' => false,
    'usefooter' => false,
    'usetoc' => false,
    'usebookmark' => false,
    'printpageident' => true,
  ],
  'pmt08' => [
    'interpretation' =>  'HIGH',
    'useheader' => false,
    'usefooter' => false,
    'usetoc' => false,
    'usebookmark' => false,
    'printpageident' => true,
  ],
  'pmt61' => [
    'interpretation' => 'CONTENT',
    'useheader' => false,
    'usefooter' => false,
    'usetoc' => false,
    'usebookmark' => false,
    'printpageident' => true,
  ],
];

return [
  'pmType' => $pmType,
  'pmEntryType' => $pmEntryType,
];