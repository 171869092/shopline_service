<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Country;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Command\Annotation\Command;
class countryCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var Country
     */
    protected $country;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('country');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
//        $this->addOption('api', '', InputOption::VALUE_REQUIRED, 'apikey', 'Hyperf');
//        $this->addOption('auth', '', InputOption::VALUE_REQUIRED, 'authkey', 'Hyperf');
//        $this->addOption('country', '', InputOption::VALUE_REQUIRED, 'country', 'Hyperf');
    }

    public function handle()
    {
        // TODO: Implement handle() method.
        $this->line("~~~ start ~~~", 'info');
        $params = [
            0 => ['code' => 'SR', 'name' => 'SURINAME'],
            1 => ['code' => 'SD', 'name' => 'SUDAN'],
            2 => ['code' => 'VC', 'name' => 'ST. VINCENT'],
            3 => ['code' => 'XM', 'name' => 'ST. MAARTEN'],
            4 => ['code' => 'LC', 'name' => 'ST. LUCIA'],
            5 => ['code' => 'KN', 'name' => 'ST. KITTS'],
            6 => ['code' => 'XE', 'name' => 'ST. EUSTATIUS'],
            7 => ['code' => 'BL', 'name' => 'ST. BARTHELEMY'],
            8 => ['code' => 'LK', 'name' => 'SRI LANKA'],
            9 => ['code' => 'ES', 'name' => 'SPAIN'],
            10 => ['code' => 'ZA', 'name' => 'SOUTH AFRICA'],
            11 => ['code' => 'XS', 'name' => 'SOMALILAND (NORTH SOMALIA)'],
            12 => ['code' => 'SO', 'name' => 'SOMALIA'],
            13 => ['code' => 'SB', 'name' => 'SOLOMON ISLANDS'],
            14 => ['code' => 'SI', 'name' => 'SLOVENIA'],
            15 => ['code' => 'SK', 'name' => 'SLOVAKIA'],
            16 => ['code' => 'SG', 'name' => 'SINGAPORE'],
            17 => ['code' => 'SL', 'name' => 'SIERRA LEONE'],
            18 => ['code' => 'SC', 'name' => 'SEYCHELLES'],
            19 => ['code' => 'RS', 'name' => 'SERBIA'],
            20 => ['code' => 'SN', 'name' => 'SENEGAL'],
            21 => ['code' => 'SA', 'name' => 'SAUDI ARABIA'],
            22 => ['code' => 'ST', 'name' => 'SAO TOME AND PRINCIPE'],
            23 => ['code' => 'SM', 'name' => 'SAN MARINO'],
            24 => ['code' => 'WS', 'name' => 'SAMOA'],
            25 => ['code' => 'MP', 'name' => 'SAIPAN'],
            26 => ['code' => 'RW', 'name' => 'RWANDA'],
            27 => ['code' => 'RU', 'name' => 'RUSSIAN FEDERATION'],
            28 => ['code' => 'RO', 'name' => 'ROMANIA'],
            29 => ['code' => 'RE', 'name' => 'REUNION'],
            30 => ['code' => 'QA', 'name' => 'QATAR'],
            31 => ['code' => 'PR', 'name' => 'PUERTO RICO'],
            32 => ['code' => 'PT', 'name' => 'PORTUGAL'],
            33 => ['code' => 'PL', 'name' => 'POLAND'],
            34 => ['code' => 'PH', 'name' => 'PHILIPPINES'],
            35 => ['code' => 'PE', 'name' => 'PERU'],
            36 => ['code' => 'PY', 'name' => 'PARAGUAY'],
            37 => ['code' => 'PG', 'name' => 'PAPUA NEW GUINEA'],
            38 => ['code' => 'PA', 'name' => 'PANAMA'],
            39 => ['code' => 'PW', 'name' => 'PALAU'],
            40 => ['code' => 'PK', 'name' => 'PAKISTAN'],
            41 => ['code' => 'OM', 'name' => 'OMAN'],
            42 => ['code' => 'NO', 'name' => 'NORWAY'],
            43 => ['code' => 'NU', 'name' => 'NIUE'],
            44 => ['code' => 'NG', 'name' => 'NIGERIA'],
            45 => ['code' => 'NE', 'name' => 'NIGER'],
            46 => ['code' => 'NI', 'name' => 'NICARAGUA'],
            47 => ['code' => 'NZ', 'name' => 'NEW ZEALAND'],
            48 => ['code' => 'NC', 'name' => 'NEW CALEDONIA'],
            49 => ['code' => 'XN', 'name' => 'NEVIS'],
            50 => ['code' => 'NL', 'name' => 'NETHERLANDS'],
            51 => ['code' => 'NP', 'name' => 'NEPAL'],
            52 => ['code' => 'NR', 'name' => 'NAURU'],
            53 => ['code' => 'NA', 'name' => 'NAMIBIA'],
            54 => ['code' => 'MM', 'name' => 'MYANMAR'],
            55 => ['code' => 'MA', 'name' => 'MOROCCO'],
            56 => ['code' => 'MS', 'name' => 'MONTSERRAT'],
            57 => ['code' => 'ME', 'name' => 'MONTENEGRO'],
            58 => ['code' => 'MN', 'name' => 'MONGOLIA'],
            59 => ['code' => 'MC', 'name' => 'MONACO'],
            60 => ['code' => 'MD', 'name' => 'MOLDOVA, REPUBLIC OF'],
            61 => ['code' => 'FM', 'name' => 'MICRONESIA, FEDERATED STATES OF'],
            62 => ['code' => 'MX', 'name' => 'MEXICO'],
            63 => ['code' => 'YT', 'name' => 'MAYOTTE'],
            64 => ['code' => 'MU', 'name' => 'MAURITIUS'],
            65 => ['code' => 'MR', 'name' => 'MAURITANIA'],
            66 => ['code' => 'MQ', 'name' => 'MARTINIQUE'],
            67 => ['code' => 'MH', 'name' => 'MARSHALL ISLANDS'],
            68 => ['code' => 'MT', 'name' => 'MALTA'],
            69 => ['code' => 'ML', 'name' => 'MALI'],
            70 => ['code' => 'MV', 'name' => 'MALDIVES'],
            71 => ['code' => 'MW', 'name' => 'MALAWI'],
            72 => ['code' => 'MY', 'name' => 'MALAYSIA'],
            73 => ['code' => 'MG', 'name' => 'MADAGASCAR'],
            74 => ['code' => 'MK', 'name' => 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF'],
            75 => ['code' => 'MO', 'name' => 'MACAU'],
            76 => ['code' => 'LU', 'name' => 'LUXEMBOURG'],
            77 => ['code' => 'LT', 'name' => 'LITHUANIA'],
            78 => ['code' => 'LI', 'name' => 'LIECHTENSTEIN'],
            79 => ['code' => 'LY', 'name' => 'LIBYA'],
            80 => ['code' => 'LR', 'name' => 'LIBERIA'],
            81 => ['code' => 'LS', 'name' => 'LESOTHO'],
            82 => ['code' => 'LB', 'name' => 'LEBANON'],
            83 => ['code' => 'LV', 'name' => 'LATVIA'],
            84 => ['code' => 'LA', 'name' => 'LAO PEOPLE\'S DEMOCRATIC REPUBLIC'],
            85 => ['code' => 'KG', 'name' => 'KYRGYZSTAN'],
            86 => ['code' => 'KW', 'name' => 'KUWAIT'],
            87 => ['code' => 'XK', 'name' => 'KOSOVO'],
            88 => ['code' => 'KR', 'name' => 'KOREA, REPUBLIC OF'],
            89 => ['code' => 'KP', 'name' => 'KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF'],
            90 => ['code' => 'KI', 'name' => 'KIRIBATI'],
            91 => ['code' => 'KE', 'name' => 'KENYA'],
            92 => ['code' => 'KZ', 'name' => 'KAZAKHSTAN'],
            93 => ['code' => 'JO', 'name' => 'JORDAN'],
            94 => ['code' => 'JE', 'name' => 'JERSEY'],
            95 => ['code' => 'JP', 'name' => 'JAPAN'],
            96 => ['code' => 'JM', 'name' => 'JAMAICA'],
            97 => ['code' => 'IT', 'name' => 'ITALY'],
            98 => ['code' => 'IL', 'name' => 'ISRAEL'],
            99 => ['code' => 'IE', 'name' => 'IRELAND'],
            100 => ['code' => 'IQ', 'name' => 'IRAQ'],
            101 => ['code' => 'IR', 'name' => 'IRAN, ISLAMIC REPUBLIC OF'],
            102 => ['code' => 'ID', 'name' => 'INDONESIA'],
            103 => ['code' => 'IN', 'name' => 'INDIA'],
            104 => ['code' => 'IS', 'name' => 'ICELAND'],
            105 => ['code' => 'HU', 'name' => 'HUNGARY'],
            106 => ['code' => 'HK', 'name' => 'HONG KONG'],
            107 => ['code' => 'HN', 'name' => 'HONDURAS'],
            108 => ['code' => 'HT', 'name' => 'HAITI'],
            109 => ['code' => 'GY', 'name' => 'GUYANA'],
            110 => ['code' => 'GW', 'name' => 'GUINEA BISSAU'],
            111 => ['code' => 'GN', 'name' => 'GUINEA REPUBLIC'],
            112 => ['code' => 'GG', 'name' => 'GUERNSEY'],
            113 => ['code' => 'GT', 'name' => 'GUATEMALA'],
            114 => ['code' => 'GU', 'name' => 'GUAM'],
            115 => ['code' => 'GP', 'name' => 'GUADELOUPE'],
            116 => ['code' => 'GD', 'name' => 'GRENADA'],
            117 => ['code' => 'GL', 'name' => 'GREENLAND'],
            118 => ['code' => 'GR', 'name' => 'GREECE'],
            119 => ['code' => 'GI', 'name' => 'GIBRALTAR'],
            120 => ['code' => 'GH', 'name' => 'GHANA'],
            121 => ['code' => 'DE', 'name' => 'GERMANY'],
            122 => ['code' => 'GE', 'name' => 'GEORGIA'],
            123 => ['code' => 'GM', 'name' => 'GAMBIA'],
            124 => ['code' => 'GA', 'name' => 'GABON'],
            125 => ['code' => 'ZW', 'name' => 'ZIMBABWE'],
            126 => ['code' => 'FR', 'name' => 'FRANCE'],
            127 => ['code' => 'FI', 'name' => 'FINLAND'],
            128 => ['code' => 'ZM', 'name' => 'ZAMBIA'],
            129 => ['code' => 'FJ', 'name' => 'FIJI'],
            130 => ['code' => 'YE', 'name' => 'YEMEN'],
            131 => ['code' => 'FO', 'name' => 'FAROE ISLANDS'],
            132 => ['code' => 'FK', 'name' => 'FALKLAND ISLANDS (MALVINAS)'],
            133 => ['code' => 'ET', 'name' => 'ETHIOPIA'],
            134 => ['code' => 'VI', 'name' => 'VIRGIN ISLANDS, U.S.'],
            135 => ['code' => 'EE', 'name' => 'ESTONIA'],
            136 => ['code' => 'VG', 'name' => 'VIRGIN ISLANDS, BRITISH'],
            137 => ['code' => 'ER', 'name' => 'ERITREA'],
            138 => ['code' => 'GQ', 'name' => 'EQUATORIAL GUINEA'],
            139 => ['code' => 'SV', 'name' => 'EL SALVADOR'],
            140 => ['code' => 'EG', 'name' => 'EGYPT'],
            141 => ['code' => 'VN', 'name' => 'VIETNAM'],
            142 => ['code' => 'EC', 'name' => 'ECUADOR'],
            143 => ['code' => 'TP', 'name' => 'EAST TIMOR'],
            144 => ['code' => 'VE', 'name' => 'VENEZUELA'],
            145 => ['code' => 'DO', 'name' => 'DOMINICAN REPUBLIC'],
            146 => ['code' => 'DM', 'name' => 'DOMINICA'],
            147 => ['code' => 'DJ', 'name' => 'DJIBOUTI'],
            148 => ['code' => 'VU', 'name' => 'VANUATU'],
            149 => ['code' => 'DK', 'name' => 'DENMARK'],
            150 => ['code' => 'CZ', 'name' => 'CZECH REPUBLIC'],
            151 => ['code' => 'CY', 'name' => 'CYPRUS'],
            152 => ['code' => 'CU', 'name' => 'CUBA'],
            153 => ['code' => 'UZ', 'name' => 'UZBEKISTAN'],
            154 => ['code' => 'UY', 'name' => 'URUGUAY'],
            155 => ['code' => 'HR', 'name' => 'CROATIA'],
            156 => ['code' => 'CI', 'name' => 'COTE D\'IVOIRE'],
            157 => ['code' => 'CR', 'name' => 'COSTA RICA'],
            158 => ['code' => 'CK', 'name' => 'COOK ISLANDS'],
            159 => ['code' => 'CD', 'name' => 'CONGO, THE DEMOCRATIC REPUBLIC OF THE'],
            160 => ['code' => 'UM', 'name' => 'UNITED STATES MINOR OUTLYING ISLANDS'],
            161 => ['code' => 'CG', 'name' => 'CONGO'],
            162 => ['code' => 'KM', 'name' => 'COMOROS'],
            163 => ['code' => 'CN', 'name' => 'CHINA'],
            164 => ['code' => 'US', 'name' => 'UNITED STATES'],
            165 => ['code' => 'GF', 'name' => 'FRENCH GUIANA'],
            166 => ['code' => 'AN', 'name' => 'NETHERLANDS ANTILLES'],
            167 => ['code' => 'MZ', 'name' => 'MOZAMBIQUE'],
            168 => ['code' => 'CW', 'name' => 'CURACAO'],
            169 => ['code' => 'CO', 'name' => 'COLOMBIA'],
            170 => ['code' => 'CL', 'name' => 'CHILE'],
            171 => ['code' => 'TD', 'name' => 'CHAD'],
            172 => ['code' => 'CF', 'name' => 'CENTRAL AFRICAN REPUBLIC'],
            173 => ['code' => 'KY', 'name' => 'CAYMAN ISLANDS'],
            174 => ['code' => 'GB', 'name' => 'UNITED KINGDOM'],
            175 => ['code' => 'CV', 'name' => 'CAPE VERDE'],
            176 => ['code' => 'AE', 'name' => 'UNITED ARAB EMIRATES'],
            177 => ['code' => 'IC', 'name' => 'CANARY ISLANDS, THE'],
            178 => ['code' => 'CA', 'name' => 'CANADA'],
            179 => ['code' => 'UA', 'name' => 'UKRAINE'],
            180 => ['code' => 'CM', 'name' => 'CAMEROON'],
            181 => ['code' => 'KH', 'name' => 'CAMBODIA'],
            182 => ['code' => 'UG', 'name' => 'UGANDA'],
            183 => ['code' => 'BI', 'name' => 'BURUNDI'],
            184 => ['code' => 'BF', 'name' => 'BURKINA FASO'],
            185 => ['code' => 'BG', 'name' => 'BULGARIA'],
            186 => ['code' => 'BN', 'name' => 'BRUNEI DARUSSALAM'],
            187 => ['code' => 'TV', 'name' => 'TUVALU'],
            188 => ['code' => 'BR', 'name' => 'BRAZIL'],
            189 => ['code' => 'TC', 'name' => 'TURKS AND CAICOS ISLANDS'],
            190 => ['code' => 'TM', 'name' => 'TURKMENISTAN'],
            191 => ['code' => 'BW', 'name' => 'BOTSWANA'],
            192 => ['code' => 'BA', 'name' => 'BOSNIA AND HERZEGOVINA'],
            193 => ['code' => 'BQ', 'name' => 'BONAIRE'],
            194 => ['code' => 'BO', 'name' => 'BOLIVIA'],
            195 => ['code' => 'BT', 'name' => 'BHUTAN'],
            196 => ['code' => 'TR', 'name' => 'TURKEY'],
            197 => ['code' => 'BM', 'name' => 'BERMUDA'],
            198 => ['code' => 'TN', 'name' => 'TUNISIA'],
            199 => ['code' => 'BJ', 'name' => 'BENIN'],
            200 => ['code' => 'TT', 'name' => 'TRINIDAD AND TOBAGO'],
            201 => ['code' => 'BZ', 'name' => 'BELIZE'],
            202 => ['code' => 'TO', 'name' => 'TONGA'],
            203 => ['code' => 'BE', 'name' => 'BELGIUM'],
            204 => ['code' => 'TG', 'name' => 'TOGO'],
            205 => ['code' => 'BY', 'name' => 'BELARUS'],
            206 => ['code' => 'BB', 'name' => 'BARBADOS'],
            207 => ['code' => 'BD', 'name' => 'BANGLADESH'],
            208 => ['code' => 'BH', 'name' => 'BAHRAIN'],
            209 => ['code' => 'TH', 'name' => 'THAILAND'],
            210 => ['code' => 'BS', 'name' => 'BAHAMAS'],
            211 => ['code' => 'TZ', 'name' => 'TANZANIA'],
            212 => ['code' => 'AZ', 'name' => 'AZERBAIJAN'],
            213 => ['code' => 'TJ', 'name' => 'TAJIKISTAN'],
            214 => ['code' => 'AT', 'name' => 'AUSTRIA'],
            215 => ['code' => 'AU', 'name' => 'AUSTRALIA'],
            216 => ['code' => 'TW', 'name' => 'TAIWAN'],
            217 => ['code' => 'PF', 'name' => 'TAHITI'],
            218 => ['code' => 'AW', 'name' => 'ARUBA'],
            219 => ['code' => 'AM', 'name' => 'ARMENIA'],
            220 => ['code' => 'SY', 'name' => 'SYRIA'],
            221 => ['code' => 'AR', 'name' => 'ARGENTINA'],
            222 => ['code' => 'AG', 'name' => 'ANTIGUA'],
            223 => ['code' => 'AI', 'name' => 'ANGUILLA'],
            224 => ['code' => 'CH', 'name' => 'SWITZERLAND'],
            225 => ['code' => 'AO', 'name' => 'ANGOLA'],
            226 => ['code' => 'AD', 'name' => 'ANDORRA'],
            227 => ['code' => 'SE', 'name' => 'SWEDEN'],
            228 => ['code' => 'AS', 'name' => 'AMERICAN SAMOA'],
            229 => ['code' => 'SZ', 'name' => 'SWAZILAND'],
            230 => ['code' => 'DZ', 'name' => 'ALGERIA'],
            231 => ['code' => 'AL', 'name' => 'ALBANIA'],
            232 => ['code' => 'AF', 'name' => 'AFGHANISTAN'],
        ];
        foreach ($params as $v){
            $this->country->insert($v);
        }
        $this->line("~~~ end ~~~", 'info');
    }
}
