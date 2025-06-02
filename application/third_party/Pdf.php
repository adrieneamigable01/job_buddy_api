<?php defined('BASEPATH') OR exit('No direct script access allowed');
use Dompdf\Dompdf;
 /**


* CodeIgniter PDF Library
 *
 * Generate PDF's in your CodeIgniter applications.
 *
 * @package         CodeIgniter
 * @subpackage      Libraries
 * @category        Libraries
 * @author          Chris Harvey
 * @license         MIT License
 * @link            https://github.com/chrisnharvey/CodeIgniter-PDF-Generator-Library



*/

require_once(dirname(__FILE__) . '/dompdf/autoload.inc.php');

class Pdf extends DOMPDF
{
    /**
     * Get an instance of CodeIgniter
     *
     * @access  protected
     * @return  void
     */
    protected function ci()
    {
        return get_instance();
    }

    /**
     * Load a CodeIgniter view into domPDF
     *
     * @access  public
     * @param   string  $view The view to load
     * @param   array   $data The view data
     * @return  void
     */
    public function load_view($view, $data = array())
    {   
        $html = $this->ci()->load->view($view, $data, TRUE);
        $this->load_html($html);
    }
    public function load_view2($name,$view, $data = array())
    {   
        
        $this->setPaper('A4', 'landscape');
        $html = $this->ci()->load->view($view, $data, TRUE);
        $this->load_html($html);
        $this->render();
        $this->stream($name."-PDS.pdf", array('Attachment'=> 0));
    }
    public function load_view2_portrait($name,$view, $data = array())
    {   
        
        $this->setPaper('legal');
        $html = $this->ci()->load->view($view, $data, TRUE);
        $this->load_html($html);
        $this->render();

        $x          = 490;
        $y          = 980;
        $text       = "Page {PAGE_NUM} of {PAGE_COUNT}";     
        $font       = $this->getFontMetrics()->get_font('Helvetica', 'normal');   
        $size       = 12;    
        $color      = array(0,0,0);
        $word_space = 0.0;
        $char_space = 0.0;
        $angle      = 0.0;

        $this->getCanvas()->page_text(
        $x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle
        );

        $this->stream($name.".pdf", array('Attachment'=> 0));
    }
    public function load_view3_portrait($name,$view, $data = array())
    {   
        
        $this->set_option('isRemoteEnabled', TRUE);
        $this->set_option('isHtml5ParserEnabled', TRUE);
        $this->setPaper('legal');
        $html = $this->ci()->load->view($view, $data, TRUE);
        $this->load_html($html);
        $this->render();
        $this->stream($name.".pdf", array('Attachment'=> 0));
    }
    public function load_view4_portrait($name,$view, $data = array())
    {   
        $this->set_option('isRemoteEnabled', TRUE);
        $this->set_option('isHtml5ParserEnabled', TRUE);
        $this->setPaper('legal', 'landscape');
        $html = $this->ci()->load->view($view, $data, TRUE);
        $this->load_html($html);
        $this->render();
        $this->stream($name.".pdf", array('Attachment'=> 0));
    }
    public function load_view2_portraitwpage($name,$view, $data = array())
    {   
        
        $this->setPaper('legal');
        $html = $this->ci()->load->view($view, $data, TRUE);
        $this->load_html($html);
        $this->render();

        // Parameters
        $x          = 505;
        $y          = 790;
        $text       = "{PAGE_NUM} of {PAGE_COUNT}";     
        $font       = $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');   
        $size       = 10;    
        $color      = array(0,0,0);
        $word_space = 0.0;
        $char_space = 0.0;
        $angle      = 0.0;

        $dompdf->getCanvas()->page_text(
        $x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle
        );


        $this->stream($name.".pdf", array('Attachment'=> 0));
    }
}
?>