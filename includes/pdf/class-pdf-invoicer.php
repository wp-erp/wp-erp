<?php
namespace WeDevs\ERP;
//require ( 'fpdf.php.php' );

class PDF_Invoicer extends FPDF {

	public $font            = 'helvetica';
	public $columnOpacity   = 0.06;
	public $columnSpacing   = 0.3;
	public $referenceformat = [ '.', ',' ];
	public $margins         = [ 'l' => 20, 't' => 20, 'r' => 20 ];

	public $barcode;
	public $watermark;
	public $l;
	public $document;
	public $type;
	public $reference = [];
	public $logo;
	public $color;
	public $issue_date = [];
	public $due_date = [];
	public $from_title;
    public $from;
	public $to_title;
	public $to;
    public $table_headers = [];
    public $first_column_width = 62;
    public $columns;
	public $items;
	public $totals;
	public $badge;
	public $addText;
	public $footernote;
	public $dimensions;
	public $angle = 0;

	// Barcode Properties
	protected $T128;
	protected $ABCset = "";
	protected $Aset   = "";
	protected $Bset   = "";
	protected $Cset   = "";
	protected $SetFrom;
	protected $SetTo;
	protected $JStart = [ "A" => 103, "B" => 104, "C" => 105 ];
	protected $JSwap  = [ "A" => 101, "B" => 100, "C" => 99 ];

	// Constructor
	public function __construct( $size = 'A4', $currency = '€', $language = 'en' ) {

		$this->items              = [];
		$this->totals             = [];
		$this->addText            = [];
		$this->maxImageDimensions = [ 230, 130 ];

		$this->setDocumentSize( $size );
		$this->set_theme_color( "#222222" );

		$this->FPDF( 'P', 'mm', [ $this->document['w'], $this->document['h'] ] );
		$this->AliasNbPages();
		$this->SetMargins( $this->margins['l'], $this->margins['t'], $this->margins['r'] );
	}

	public function set_type( $title ) {
		$this->title = $title;
	}

	public function set_barcode( $code ) {
		$this->barcode = $code;
	}

	public function set_theme_color( $rgbcolor ) {
		$this->color = $this->hex2rgb( $rgbcolor );
	}

    public function set_reference( $value, $title ) {
        $this->reference[] = [
            'value' => $value,
            'title' => $title
        ];
    }

	public function set_logo( $logo = 0, $maxWidth = 0, $maxHeight = 0 ) {

		if ( $maxWidth and $maxHeight ) {
			$this->maxImageDimensions = [ $maxWidth, $maxHeight ];
		}

		$this->logo       = $logo;
		$this->dimensions = $this->resizeToFit( $logo );
	}

    /**
     * Set title for the from address
     * @return string
     */
    public function set_from_title( $title = '' ) {
        $this->from_title = $title ? $title : 'From';
    }

	public function set_from( $data ) {
		$this->from = $data;
	}

    /**
     * Set title for the from address
     * @return string
     */
    public function set_to_title( $title = '' ) {
        $this->to_title = $title ? $title : 'To';
    }

	public function set_to_address( $data ) {
		$this->to = $data;
	}

    /**
     * Set Table Headers
     */
    public function set_table_headers( $headers = [] ) {
        $this->table_headers = $headers;
    }

    /**
     * Get table headers count
     */
    public function get_table_headers_count() {
        return count( $this->table_headers );
    }

    // Set first column width
    public function set_first_column_width( $width ) {
        $this->first_column_width = $width;
    }

	public function set_number_format( $decimals, $thousands_sep ) {
		$this->referenceformat = [ $decimals, $thousands_sep ];
	}

	public function flipflop() {
		$this->flipflop = true;
	}

	public function add_item(  ) {

        $item = func_get_args();

		$this->items[] = $item;
	}

	public function add_total( $name, $value, $colored = 0 ) {

        $t['name']      = $name;
        $t['value']     = $value;
        $t['colored']   = $colored;
        $this->totals[] = $t;
	}

	public function add_title( $title ) {
		$this->addText[] = [ 'title', $title ];
	}

	public function add_paragraph( $paragraph ) {

		$paragraph       = $this->br2nl( $paragraph );
		$this->addText[] = [ 'paragraph', $paragraph ];
	}

	public function add_badge( $badge ) {
		$this->badge = $badge;
	}

	public function set_footer_note( $note ) {
		$this->footernote = $note;
	}

	public function render( $name = '', $destination = '' ) {

		$this->AddPage();
		$this->Body();
		$this->AliasNbPages();
		$this->Output( $name, $destination );
	}

	/*******************************************************************************
	*                                                                              *
	*                               Create Invoice                                 *
	*                                                                              *
	*******************************************************************************/
	public function Header() {

		if ( isset( $this->logo ) ) {
			$this->Image( $this->logo, $this->margins['l'], $this->margins['t'], $this->dimensions[0], $this->dimensions[1] );
		}

        // Barcode
        if ( $this->barcode && 1 == $this->PageNo() ) {
            $this->SetFont( 'Arial', '', 8 );
            $this->set_barcode_data();
            $this->Code128( 130, 10, $this->barcode, 60, 15 );
        }

        //Title
        $this->SetTextColor( 0, 0, 0 );
        $this->SetFont( $this->font, 'B', 20 );
        $this->Cell( 0, 5, iconv( "UTF-8", "ISO-8859-1", strtoupper( $this->title ) ), 0, 1, 'C' );
        $this->Ln( 5 );

        $lineheight = 5;
        //Calculate position of strings
        $this->SetFont( $this->font, 'B', 9 );

        $temp_ref = [];

        foreach ( $this->reference as $ref ) {
            foreach ( $ref as $key => $value ) {
                if ( 'title' == $key ) {
                    $temp_ref[] = $value;
                }
            }
        }

        $max_ref_width =  max( array_map( [$this, 'GetStringWidth'], $temp_ref ) );

        $positionX = $this->document['w'] - $this->margins['l'] - $this->margins['r'] - $max_ref_width - 35;

        if ( $this->reference ) {

            if ( is_array( $this->reference ) ) {

                foreach ( $this->reference as $data ) {

                    $this->Cell( $positionX, $lineheight );
                    $this->SetFont( $this->font, 'B', 9 );
                    $this->SetTextColor( $this->color[0], $this->color[1], $this->color[2] );
                    $this->Cell( 32, $lineheight, iconv( "UTF-8", "ISO-8859-1", $data['title'] ) . ':', 0, 0, 'L' );
                    $this->SetTextColor( 50, 50, 50 );
                    $this->SetFont( $this->font, '', 9 );
                    $this->Cell( 0, $lineheight, $data['value'], 0, 1, 'R' );
                }
            }
        }

		//First page
		if ( $this->PageNo() == 1 ) {

			if ( ( $this->margins['t'] + $this->dimensions[1] ) > $this->GetY() ) {
				$this->SetY( $this->margins['t'] + $this->dimensions[1] + 10 );
			} else {
				$this->SetY( $this->GetY() + 10 );
			}
			$this->Ln( 5 );
			$this->SetTextColor( $this->color[0], $this->color[1], $this->color[2] );
			$this->SetDrawColor( $this->color[0], $this->color[1], $this->color[2] );
			$this->SetFont( $this->font, 'B', 10 );
			$width = ( $this->document['w'] - $this->margins['l'] - $this->margins['r'] ) / 2;
			if ( isset( $this->flipflop ) ) {
				$to              = $this->to_title;
				$from            = $this->from_title;
				$this->l['to']   = $from;
				$this->l['from'] = $to;
				$to              = $this->to;
				$from            = $this->from;
				$this->to        = $from;
				$this->from      = $to;
			}
			$this->Cell( $width, $lineheight, $this->from_title, 0, 0, 'L' );
			$this->Cell( 0, $lineheight, $this->to_title, 0, 0, 'L' );
			$this->Ln( 7 );
			$this->SetLineWidth( 0.3 );
			$this->Line( $this->margins['l'], $this->GetY(), $this->margins['l'] + $width - 10, $this->GetY() );
			$this->Line( $this->margins['l'] + $width, $this->GetY(), $this->margins['l'] + $width + $width, $this->GetY() );

			//Information
			$this->Ln( 5 );
			$this->SetTextColor( 50, 50, 50 );
			$this->SetFont( $this->font, 'B', 10 );
			$this->Cell( $width, $lineheight, $this->from[0], 0, 0, 'L' );
			$this->Cell( 0, $lineheight, $this->to[0], 0, 0, 'L' );
			$this->SetFont( $this->font, '', 8 );
			$this->SetTextColor( 100, 100, 100 );
			$this->Ln( 7 );
			for ( $i = 1; $i < max( count( $this->from ), count( $this->to ) ); $i++ ) {
				$this->Cell( $width, $lineheight, iconv( "UTF-8", "ISO-8859-1", isset( $this->from[$i] ) ? $this->from[$i] : '' ), 0, 0, 'L' );
				$this->Cell( 0, $lineheight, iconv( "UTF-8", "ISO-8859-1", isset( $this->to[$i] ) ? $this->to[$i] : '' ), 0, 0, 'L' );
				$this->Ln( 5 );
			}
			$this->Ln( -6 );
		}
		$this->Ln( 5 );

        $this->columns = $this->get_table_headers_count();


		//Table header
		if ( !isset( $this->productsEnded ) ) {
			$width_other = ( $this->document['w'] - $this->margins['l'] - $this->margins['r'] - $this->first_column_width - ( $this->columns * $this->columnSpacing ) ) / ( $this->columns - 1 );
			$this->SetTextColor( 50, 50, 50 );
			$this->Ln( 12 );
			$this->SetFont( $this->font, 'B', 9 );

            foreach ( $this->table_headers as $key => $header ) {
                if ( 0 == $key ) {
                    $this->Cell( 1, 10, '', 0, 0, 'L', 0 );
                    $this->Cell( $this->first_column_width, 10, iconv( "UTF-8", "ISO-8859-1", $header ), 0, 0, 'L', 0 );
                } else {
                    $this->Cell( $this->columnSpacing, 10, '', 0, 0, 'L', 0 );
                    $this->Cell( $width_other, 10, iconv( "UTF-8", "ISO-8859-1", $header ), 0, 0, 'C', 0 );
                }
            }

			$this->Ln();
			$this->SetLineWidth( 0.3 );
			$this->SetDrawColor( $this->color[0], $this->color[1], $this->color[2] );
			$this->Line( $this->margins['l'], $this->GetY(), $this->document['w'] - $this->margins['r'], $this->GetY() );
			$this->Ln( 2 );
		} else {
			$this->Ln( 12 );
		}
	}

	public function Body() {

		$width_other = ( $this->document['w'] - $this->margins['l'] - $this->margins['r'] - $this->first_column_width - ( $this->columns * $this->columnSpacing ) ) / ( $this->columns - 1 );
		$cellHeight  = 9;
		$bgcolor     = ( 1 - $this->columnOpacity ) * 255;

		if ( $this->items ) {

			foreach ( $this->items as $item ) {

				if ( isset( $item[0][1] ) ) {

					$calculated_height = new WeDevs_PDF_Invoicer();
					$calculated_height->addPage();
					$calculated_height->setXY(0,0);
					$calculated_height->setFont($this->font, '', 7);
					$calculated_height->MultiCell($this->first_column_width, 3, iconv('UTF-8', 'ISO-8859-1', $item[0][1]), 0, 'L', 1 );
					$page_height = $this->document['h'] - $this->GetY() - $this->margins['t'] - $this->margins['t'];

					if ( $page_height < 0 ) {
						$this->AddPage();
					}
				}

				$cHeight = $cellHeight;

				foreach ( $item as $key => $column ) {

					if ( 0 == $key ) {
						$this->SetFont( $this->font, 'b', 8 );
						$this->SetTextColor(50, 50, 50 );
						$this->SetFillColor( $bgcolor, $bgcolor, $bgcolor );
						$this->Cell( 1, $cHeight, '', 0, 0, 'L', 1 );
						$x = $this->GetX();
						$this->Cell( $this->first_column_width, $cHeight, iconv( 'UTF-8', 'ISO-8859-1', $column[0] ), 0, 0, 'L', 1 );

						if ( isset( $column[1] ) ) {
							$resetX = $this->GetX();
							$resetY = $this->GetY();
							$this->SetTextColor( 120, 120, 120 );
							$this->SetXY( $x, $this->GetY() + 8 );
							$this->SetFont( $this->font, '', 7 );
							$this->MultiCell( $this->first_column_width, 3, iconv( 'UTF-8', 'ISO-8859-1', $column[1] ), 0, 'L', 1 );
							$newY = $this->GetY();
							$cHeight = $newY - $resetY + 2;
							$this->SetXY( $x - 1, $resetY );
							$this->Cell( 1, $cHeight, '', 0, 0, 'L', 1);
							$this->SetXY( $x, $newY );
							$this->Cell( $this->first_column_width, 2, '', 0, 0, 'L', 1 );
							$this->SetXY( $resetX, $resetY );
						}

						$this->Cell( $this->columnSpacing, $cHeight, '', 0, 0, 'R', 0 );
						continue;
					}

					$this->SetFont($this->font,'',8);
					$this->SetTextColor(50,50,50);
					$this->SetFillColor($bgcolor, $bgcolor, $bgcolor);
					$this->Cell( $width_other, $cHeight, $column, 0, 0, 'C', 1 );
					$this->Cell( $this->columnSpacing, $cHeight, '', 0, 0, 'R', 0 );
//					$this->Cell( $this->columnSpacing, $cHeight, '', 0, 0, 'R', 0 );
				}

				$this->Ln();
				$this->Ln( $this->columnSpacing );
			}
		}

		$badgeX = $this->getX();
		$badgeY = $this->getY();

		//Add totals
		if ( $this->totals ) {
			foreach ( $this->totals as $total ) {
				$this->SetTextColor( 50, 50, 50 );
				$this->SetFillColor( $bgcolor, $bgcolor, $bgcolor );
				$this->Cell( 1 + $this->first_column_width, $cellHeight, '', 0, 0, 'L', 0 );
				for ( $i = 0; $i < $this->columns - 3; $i++ ) {
					$this->Cell( $width_other, $cellHeight, '', 0, 0, 'L', 0 );
					$this->Cell( $this->columnSpacing, $cellHeight, '', 0, 0, 'L', 0 );
				}
				$this->Cell( $this->columnSpacing, $cellHeight, '', 0, 0, 'L', 0 );
				if ( $total['colored'] ) {
					$this->SetTextColor( 255, 255, 255 );
					$this->SetFillColor( $this->color[0], $this->color[1], $this->color[2] );
				}
				$this->SetFont( $this->font, 'b', 8 );
				$this->Cell( 1, $cellHeight, '', 0, 0, 'L', 1 );
				$this->Cell( $width_other - 1, $cellHeight, iconv( 'UTF-8', 'windows-1252', $total['name'] ), 0, 0, 'L', 1 );
				$this->Cell( $this->columnSpacing, $cellHeight, '', 0, 0, 'L', 0 );
				$this->SetFont( $this->font, 'b', 8 );
				$this->SetFillColor( $bgcolor, $bgcolor, $bgcolor );
				if ( $total['colored'] ) {
					$this->SetTextColor( 255, 255, 255 );
					$this->SetFillColor( $this->color[0], $this->color[1], $this->color[2] );
				}
				$this->Cell( $width_other, $cellHeight, iconv( 'UTF-8', 'windows-1252', $total['value'] ), 0, 0, 'C', 1 );
				$this->Ln();
				$this->Ln( $this->columnSpacing );
			}
		}
		$this->productsEnded = true;
		$this->Ln();
		$this->Ln( 3 );

		// Watermark
		if ( $this->watermark ) {
			$this->SetFont( 'Arial', 'B', 60 );
			$this->SetTextColor( 255, 192, 203 );
			$this->RotatedText( 85, 160, $this->watermark, 45 );
		}
		//Badge
		if ( $this->badge ) {
			$badge  = ' ' . strtoupper( $this->badge ) . ' ';
			$resetX = $this->getX();
			$resetY = $this->getY();
//			$this->setXY( $badgeX, $badgeY );
			$this->SetLineWidth( 0.4 );
			$this->SetDrawColor( $this->color[0], $this->color[1], $this->color[2] );
			$this->setTextColor( $this->color[0], $this->color[1], $this->color[2] );
			$this->SetFont( $this->font, 'b', 15 );
			$this->Rotate( 10, $this->getX(), $this->getY() );
			$this->Rect( $this->GetX(), $this->GetY(), $this->GetStringWidth( $badge ) + 2, 10 );
			$this->Write( 10, $badge );
			$this->Rotate( 0 );
			if ( $resetY > $this->getY() + 20 ) {
				$this->setXY( $resetX, $resetY );
			} else {
				$this->Ln( 18 );
			}
		}

		//Add information
		foreach ( $this->addText as $text ) {
			if ( $text[0] == 'title' ) {
				$this->SetFont( $this->font, 'b', 9 );
				$this->SetTextColor( 50, 50, 50 );
				$this->Cell( 0, 10, iconv( "UTF-8", "ISO-8859-1", strtoupper( $text[1] ) ), 0, 0, 'L', 0 );
				$this->Ln();
				$this->SetLineWidth( 0.3 );
				$this->SetDrawColor( $this->color[0], $this->color[1], $this->color[2] );
				$this->Line( $this->margins['l'], $this->GetY(), $this->document['w'] - $this->margins['r'], $this->GetY() );
				$this->Ln( 4 );
			}
			if ( $text[0] == 'paragraph' ) {
				$this->SetTextColor( 80, 80, 80 );
				$this->SetFont( $this->font, '', 8 );
				$this->MultiCell( 0, 4, iconv( "UTF-8", "ISO-8859-1", $text[1] ), 0, 'L', 0 );
				$this->Ln( 4 );
			}
		}
	}

	function Footer() {

		$this->SetY( -$this->margins['t'] );
		$this->SetFont( $this->font, '', 8 );
		$this->SetTextColor( 50, 50, 50 );
		$this->Cell( 0, 10, $this->footernote, 0, 0, 'L' );
		$this->Cell( 0, 10, $this->l['page'] . ' ' . $this->PageNo() . ' ' . 'of' . ' {nb}', 0, 0, 'R' );
	}

	/*******************************************************************************
	*                                                                              *
	*                               Private methods                                *
	*                                                                              *
	*******************************************************************************/

	private function setDocumentSize( $dsize ) {

		switch ( $dsize ) {
			case 'A4':
				$document['w'] = 210;
				$document['h'] = 297;
				break;
			case 'letter':
				$document['w'] = 215.9;
				$document['h'] = 279.4;
				break;
			case 'legal':
				$document['w'] = 215.9;
				$document['h'] = 355.6;
				break;
			default:
				$document['w'] = 210;
				$document['h'] = 297;
				break;
		}
		$this->document = $document;
	}

	private function resizeToFit( $image ) {

		list( $width, $height ) = getimagesize( $image );
		$newWidth  = $this->maxImageDimensions[0] / $width;
		$newHeight = $this->maxImageDimensions[1] / $height;
		$scale     = min( $newWidth, $newHeight );

		return [
			round( $this->pixelsToMM( $scale * $width ) ),
			round( $this->pixelsToMM( $scale * $height ) )
		];
	}

	private function pixelsToMM( $val ) {

		$mm_inch = 25.4;
		$dpi     = 96;

		return $val * $mm_inch / $dpi;
	}

	private function hex2rgb( $hex ) {

		$hex = str_replace( "#", "", $hex );

		if ( strlen( $hex ) == 3 ) {
			$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
			$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
			$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
		} else {
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );
		}
		$rgb = [ $r, $g, $b ];

		return $rgb;
	}

	private function br2nl( $string ) {

		return preg_replace( '/\<br(\s*)?\/?\>/i', "\n", $string );
	}

	//____________________________ Extension du constructeur _______________________
	function set_barcode_data() {

		$this->T128[] = [ 2, 1, 2, 2, 2, 2 ];           //0 : [ ]               // composition des caractères
		$this->T128[] = [ 2, 2, 2, 1, 2, 2 ];           //1 : [!]
		$this->T128[] = [ 2, 2, 2, 2, 2, 1 ];           //2 : ["]
		$this->T128[] = [ 1, 2, 1, 2, 2, 3 ];           //3 : [#]
		$this->T128[] = [ 1, 2, 1, 3, 2, 2 ];           //4 : [$]
		$this->T128[] = [ 1, 3, 1, 2, 2, 2 ];           //5 : [%]
		$this->T128[] = [ 1, 2, 2, 2, 1, 3 ];           //6 : [&]
		$this->T128[] = [ 1, 2, 2, 3, 1, 2 ];           //7 : [']
		$this->T128[] = [ 1, 3, 2, 2, 1, 2 ];           //8 : [(]
		$this->T128[] = [ 2, 2, 1, 2, 1, 3 ];           //9 : [)]
		$this->T128[] = [ 2, 2, 1, 3, 1, 2 ];           //10 : [*]
		$this->T128[] = [ 2, 3, 1, 2, 1, 2 ];           //11 : [+]
		$this->T128[] = [ 1, 1, 2, 2, 3, 2 ];           //12 : [,]
		$this->T128[] = [ 1, 2, 2, 1, 3, 2 ];           //13 : [-]
		$this->T128[] = [ 1, 2, 2, 2, 3, 1 ];           //14 : [.]
		$this->T128[] = [ 1, 1, 3, 2, 2, 2 ];           //15 : [/]
		$this->T128[] = [ 1, 2, 3, 1, 2, 2 ];           //16 : [0]
		$this->T128[] = [ 1, 2, 3, 2, 2, 1 ];           //17 : [1]
		$this->T128[] = [ 2, 2, 3, 2, 1, 1 ];           //18 : [2]
		$this->T128[] = [ 2, 2, 1, 1, 3, 2 ];           //19 : [3]
		$this->T128[] = [ 2, 2, 1, 2, 3, 1 ];           //20 : [4]
		$this->T128[] = [ 2, 1, 3, 2, 1, 2 ];           //21 : [5]
		$this->T128[] = [ 2, 2, 3, 1, 1, 2 ];           //22 : [6]
		$this->T128[] = [ 3, 1, 2, 1, 3, 1 ];           //23 : [7]
		$this->T128[] = [ 3, 1, 1, 2, 2, 2 ];           //24 : [8]
		$this->T128[] = [ 3, 2, 1, 1, 2, 2 ];           //25 : [9]
		$this->T128[] = [ 3, 2, 1, 2, 2, 1 ];           //26 : [:]
		$this->T128[] = [ 3, 1, 2, 2, 1, 2 ];           //27 : [;]
		$this->T128[] = [ 3, 2, 2, 1, 1, 2 ];           //28 : [<]
		$this->T128[] = [ 3, 2, 2, 2, 1, 1 ];           //29 : [=]
		$this->T128[] = [ 2, 1, 2, 1, 2, 3 ];           //30 : [>]
		$this->T128[] = [ 2, 1, 2, 3, 2, 1 ];           //31 : [?]
		$this->T128[] = [ 2, 3, 2, 1, 2, 1 ];           //32 : [@]
		$this->T128[] = [ 1, 1, 1, 3, 2, 3 ];           //33 : [A]
		$this->T128[] = [ 1, 3, 1, 1, 2, 3 ];           //34 : [B]
		$this->T128[] = [ 1, 3, 1, 3, 2, 1 ];           //35 : [C]
		$this->T128[] = [ 1, 1, 2, 3, 1, 3 ];           //36 : [D]
		$this->T128[] = [ 1, 3, 2, 1, 1, 3 ];           //37 : [E]
		$this->T128[] = [ 1, 3, 2, 3, 1, 1 ];           //38 : [F]
		$this->T128[] = [ 2, 1, 1, 3, 1, 3 ];           //39 : [G]
		$this->T128[] = [ 2, 3, 1, 1, 1, 3 ];           //40 : [H]
		$this->T128[] = [ 2, 3, 1, 3, 1, 1 ];           //41 : [I]
		$this->T128[] = [ 1, 1, 2, 1, 3, 3 ];           //42 : [J]
		$this->T128[] = [ 1, 1, 2, 3, 3, 1 ];           //43 : [K]
		$this->T128[] = [ 1, 3, 2, 1, 3, 1 ];           //44 : [L]
		$this->T128[] = [ 1, 1, 3, 1, 2, 3 ];           //45 : [M]
		$this->T128[] = [ 1, 1, 3, 3, 2, 1 ];           //46 : [N]
		$this->T128[] = [ 1, 3, 3, 1, 2, 1 ];           //47 : [O]
		$this->T128[] = [ 3, 1, 3, 1, 2, 1 ];           //48 : [P]
		$this->T128[] = [ 2, 1, 1, 3, 3, 1 ];           //49 : [Q]
		$this->T128[] = [ 2, 3, 1, 1, 3, 1 ];           //50 : [R]
		$this->T128[] = [ 2, 1, 3, 1, 1, 3 ];           //51 : [S]
		$this->T128[] = [ 2, 1, 3, 3, 1, 1 ];           //52 : [T]
		$this->T128[] = [ 2, 1, 3, 1, 3, 1 ];           //53 : [U]
		$this->T128[] = [ 3, 1, 1, 1, 2, 3 ];           //54 : [V]
		$this->T128[] = [ 3, 1, 1, 3, 2, 1 ];           //55 : [W]
		$this->T128[] = [ 3, 3, 1, 1, 2, 1 ];           //56 : [X]
		$this->T128[] = [ 3, 1, 2, 1, 1, 3 ];           //57 : [Y]
		$this->T128[] = [ 3, 1, 2, 3, 1, 1 ];           //58 : [Z]
		$this->T128[] = [ 3, 3, 2, 1, 1, 1 ];           //59 : [[]
		$this->T128[] = [ 3, 1, 4, 1, 1, 1 ];           //60 : [\]
		$this->T128[] = [ 2, 2, 1, 4, 1, 1 ];           //61 : []]
		$this->T128[] = [ 4, 3, 1, 1, 1, 1 ];           //62 : [^]
		$this->T128[] = [ 1, 1, 1, 2, 2, 4 ];           //63 : [_]
		$this->T128[] = [ 1, 1, 1, 4, 2, 2 ];           //64 : [`]
		$this->T128[] = [ 1, 2, 1, 1, 2, 4 ];           //65 : [a]
		$this->T128[] = [ 1, 2, 1, 4, 2, 1 ];           //66 : [b]
		$this->T128[] = [ 1, 4, 1, 1, 2, 2 ];           //67 : [c]
		$this->T128[] = [ 1, 4, 1, 2, 2, 1 ];           //68 : [d]
		$this->T128[] = [ 1, 1, 2, 2, 1, 4 ];           //69 : [e]
		$this->T128[] = [ 1, 1, 2, 4, 1, 2 ];           //70 : [f]
		$this->T128[] = [ 1, 2, 2, 1, 1, 4 ];           //71 : [g]
		$this->T128[] = [ 1, 2, 2, 4, 1, 1 ];           //72 : [h]
		$this->T128[] = [ 1, 4, 2, 1, 1, 2 ];           //73 : [i]
		$this->T128[] = [ 1, 4, 2, 2, 1, 1 ];           //74 : [j]
		$this->T128[] = [ 2, 4, 1, 2, 1, 1 ];           //75 : [k]
		$this->T128[] = [ 2, 2, 1, 1, 1, 4 ];           //76 : [l]
		$this->T128[] = [ 4, 1, 3, 1, 1, 1 ];           //77 : [m]
		$this->T128[] = [ 2, 4, 1, 1, 1, 2 ];           //78 : [n]
		$this->T128[] = [ 1, 3, 4, 1, 1, 1 ];           //79 : [o]
		$this->T128[] = [ 1, 1, 1, 2, 4, 2 ];           //80 : [p]
		$this->T128[] = [ 1, 2, 1, 1, 4, 2 ];           //81 : [q]
		$this->T128[] = [ 1, 2, 1, 2, 4, 1 ];           //82 : [r]
		$this->T128[] = [ 1, 1, 4, 2, 1, 2 ];           //83 : [s]
		$this->T128[] = [ 1, 2, 4, 1, 1, 2 ];           //84 : [t]
		$this->T128[] = [ 1, 2, 4, 2, 1, 1 ];           //85 : [u]
		$this->T128[] = [ 4, 1, 1, 2, 1, 2 ];           //86 : [v]
		$this->T128[] = [ 4, 2, 1, 1, 1, 2 ];           //87 : [w]
		$this->T128[] = [ 4, 2, 1, 2, 1, 1 ];           //88 : [x]
		$this->T128[] = [ 2, 1, 2, 1, 4, 1 ];           //89 : [y]
		$this->T128[] = [ 2, 1, 4, 1, 2, 1 ];           //90 : [z]
		$this->T128[] = [ 4, 1, 2, 1, 2, 1 ];           //91 : [{]
		$this->T128[] = [ 1, 1, 1, 1, 4, 3 ];           //92 : [|]
		$this->T128[] = [ 1, 1, 1, 3, 4, 1 ];           //93 : [}]
		$this->T128[] = [ 1, 3, 1, 1, 4, 1 ];           //94 : [~]
		$this->T128[] = [ 1, 1, 4, 1, 1, 3 ];           //95 : [DEL]
		$this->T128[] = [ 1, 1, 4, 3, 1, 1 ];           //96 : [FNC3]
		$this->T128[] = [ 4, 1, 1, 1, 1, 3 ];           //97 : [FNC2]
		$this->T128[] = [ 4, 1, 1, 3, 1, 1 ];           //98 : [SHIFT]
		$this->T128[] = [ 1, 1, 3, 1, 4, 1 ];           //99 : [Cswap]
		$this->T128[] = [ 1, 1, 4, 1, 3, 1 ];           //100 : [Bswap]
		$this->T128[] = [ 3, 1, 1, 1, 4, 1 ];           //101 : [Aswap]
		$this->T128[] = [ 4, 1, 1, 1, 3, 1 ];           //102 : [FNC1]
		$this->T128[] = [ 2, 1, 1, 4, 1, 2 ];           //103 : [Astart]
		$this->T128[] = [ 2, 1, 1, 2, 1, 4 ];           //104 : [Bstart]
		$this->T128[] = [ 2, 1, 1, 2, 3, 2 ];           //105 : [Cstart]
		$this->T128[] = [ 2, 3, 3, 1, 1, 1 ];           //106 : [STOP]
		$this->T128[] = [ 2, 1 ];                       //107 : [END BAR]

		for ( $i = 32; $i <= 95; $i++ ) {                                            // jeux de caractères
			$this->ABCset .= chr( $i );
		}
		$this->Aset = $this->ABCset;
		$this->Bset = $this->ABCset;

		for ( $i = 0; $i <= 31; $i++ ) {
			$this->ABCset .= chr( $i );
			$this->Aset .= chr( $i );
		}
		for ( $i = 96; $i <= 127; $i++ ) {
			$this->ABCset .= chr( $i );
			$this->Bset .= chr( $i );
		}
		for ( $i = 200; $i <= 210; $i++ ) {                                           // controle 128
			$this->ABCset .= chr( $i );
			$this->Aset .= chr( $i );
			$this->Bset .= chr( $i );
		}
		$this->Cset = "0123456789" . chr( 206 );

		for ( $i = 0; $i < 96; $i++ ) {                                                   // convertisseurs des jeux A & B
			@$this->SetFrom["A"] .= chr( $i );
			@$this->SetFrom["B"] .= chr( $i + 32 );
			@$this->SetTo["A"] .= chr( ( $i < 32 ) ? $i + 64 : $i - 32 );
			@$this->SetTo["B"] .= chr( $i );
		}
		for ( $i = 96; $i < 107; $i++ ) {                                                 // contrôle des jeux A & B
			@$this->SetFrom["A"] .= chr( $i + 104 );
			@$this->SetFrom["B"] .= chr( $i + 104 );
			@$this->SetTo["A"] .= chr( $i );
			@$this->SetTo["B"] .= chr( $i );
		}
	}

	function Code128( $x, $y, $code, $w, $h ) {

		$Aguid = "";                                                                      // Création des guides de choix ABC
		$Bguid = "";
		$Cguid = "";
		for ( $i = 0; $i < strlen( $code ); $i++ ) {
			$needle = substr( $code, $i, 1 );
			$Aguid .= ( ( strpos( $this->Aset, $needle ) === false ) ? "N" : "O" );
			$Bguid .= ( ( strpos( $this->Bset, $needle ) === false ) ? "N" : "O" );
			$Cguid .= ( ( strpos( $this->Cset, $needle ) === false ) ? "N" : "O" );
		}

		$SminiC = "OOOO";
		$IminiC = 4;

		$crypt = "";
		while ( $code > "" ) {
			// BOUCLE PRINCIPALE DE CODAGE
			$i = strpos( $Cguid, $SminiC );                                                // forçage du jeu C, si possible
			if ( $i !== false ) {
				$Aguid [$i] = "N";
				$Bguid [$i] = "N";
			}

			if ( substr( $Cguid, 0, $IminiC ) == $SminiC ) {                                  // jeu C
				$crypt .= chr( ( $crypt > "" ) ? $this->JSwap["C"] : $this->JStart["C"] );  // début Cstart, sinon Cswap
				$made = strpos( $Cguid, "N" );                                             // étendu du set C
				if ( $made === false ) {
					$made = strlen( $Cguid );
				}
				if ( fmod( $made, 2 ) == 1 ) {
					$made--;                                                            // seulement un nombre pair
				}
				for ( $i = 0; $i < $made; $i += 2 ) {
					$crypt .= chr( strval( substr( $code, $i, 2 ) ) );                          // conversion 2 par 2
				}
				$jeu = "C";
			} else {
				$madeA = strpos( $Aguid, "N" );                                            // étendu du set A
				if ( $madeA === false ) {
					$madeA = strlen( $Aguid );
				}
				$madeB = strpos( $Bguid, "N" );                                            // étendu du set B
				if ( $madeB === false ) {
					$madeB = strlen( $Bguid );
				}
				$made = ( ( $madeA < $madeB ) ? $madeB : $madeA );                         // étendu traitée
				$jeu  = ( ( $madeA < $madeB ) ? "B" : "A" );                                // Jeu en cours

				$crypt .= chr( ( $crypt > "" ) ? $this->JSwap[$jeu] : $this->JStart[$jeu] ); // début start, sinon swap

				$crypt .= strtr( substr( $code, 0, $made ), $this->SetFrom[$jeu], $this->SetTo[$jeu] ); // conversion selon jeu

			}
			$code  = substr( $code, $made );                                           // raccourcir légende et guides de la zone traitée
			$Aguid = substr( $Aguid, $made );
			$Bguid = substr( $Bguid, $made );
			$Cguid = substr( $Cguid, $made );
		}                                                                          // FIN BOUCLE PRINCIPALE

		$check = ord( $crypt[0] );                                                   // calcul de la somme de contrôle
		for ( $i = 0; $i < strlen( $crypt ); $i++ ) {
			$check += ( ord( $crypt[$i] ) * $i );
		}
		$check %= 103;

		$crypt .= chr( $check ) . chr( 106 ) . chr( 107 );                               // Chaine cryptée complète

		$i     = ( strlen( $crypt ) * 11 ) - 8;                                            // calcul de la largeur du module
		$modul = $w / $i;

		for ( $i = 0; $i < strlen( $crypt ); $i++ ) {                                      // BOUCLE D'IMPRESSION
			$c = $this->T128[ord( $crypt[$i] )];
			for ( $j = 0; $j < count( $c ); $j++ ) {
				$this->Rect( $x, $y, $c[$j] * $modul, $h, "F" );
				$x += ( $c[$j++] + $c[$j] ) * $modul;
			}
		}
	}

	function RotatedText( $x, $y, $txt, $angle ) {

		//Text rotated around its origin
		$this->Rotate( $angle, $x, $y );
		$this->Text( $x, $y, $txt );
		$this->Rotate( 0 );
	}

	function Rotate( $angle, $x = -1, $y = -1 ) {

		if ( $x == -1 )
			$x = $this->x;
		if ( $y == -1 )
			$y = $this->y;
		if ( $this->angle != 0 )
			$this->_out( 'Q' );
		$this->angle = $angle;
		if ( $angle != 0 ) {
			$angle *= M_PI / 180;
			$c  = cos( $angle );
			$s  = sin( $angle );
			$cx = $x * $this->k;
			$cy = ( $this->h - $y ) * $this->k;
			$this->_out( sprintf( 'q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy ) );
		}
	}

	function _endpage() {

		if ( $this->angle != 0 ) {
			$this->angle = 0;
			$this->_out( 'Q' );
		}
		parent::_endpage();
	}

}