<?php 
class ControllerExtensionModuleComoHtmlCode extends Controller {
    public function index($setting) {
        static $module = 0;
        $data['module'] = $module++;

        //$this->load->language('extension/module/como_htmlcode');
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();

        // Stylesheet - module default, or custom given
        if ($setting['stylesheet']) {
            if (file_exists('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/' . $setting['stylesheet'])) {
                $this->document->addStyle('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/' . $setting['stylesheet']);
            } else {
                $this->document->addStyle('catalog/view/theme/default/stylesheet/' . $setting['stylesheet']);
            }
        }

        $data['block_title'] = $setting['block_title'][$this->config->get('config_language_id')];
        if ($setting['html_code_status']) {
            $data['content'] = html_entity_decode($setting['como_htmlcode'][$this->config->get('config_language_id')]);
        } else {
            $data['content'] = '';
        }
        $data['block_link'] = $setting['block_link'];
        
        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $httpimagedir = HTTPS_SERVER . 'image/';
        } else {
            $httpimagedir = HTTP_SERVER . 'image/';
        }
        
        if ($setting['title_image']) {
            $data['icon'] = $httpimagedir . $setting['title_image'];
        } else {
            $data['icon'] = false;
        }
        if ($setting['border']==1) {
            $data['show_border'] = $setting['border'];
        } else {
            $data['show_border'] = false;
        }
        
        // Render image block, if any
        if ($setting['img_block_status']) {
            $data['img_block_background'] = '';
            if ($setting['img_block_background']) {
                $data['img_block_background'] = $httpimagedir . $setting['img_block_background'];
            }
            $data['img_number'] = $setting['img_number'];
            $data['img_block_links'] = $setting['img_block_links'];
            for ($i = 1; $i <= $setting['img_number']; $i++) {
                $data['img_block_images'][$i] = $setting['img_block_images'][$i] ? $httpimagedir . $setting['img_block_images'][$i] : '';
                $data['img_block_images1'][$i] = $setting['img_block_images1'][$i] ? $httpimagedir . $setting['img_block_images1'][$i] : '';
                // Search title in current language
                $data['img_block_titles'][$i] = '';
                foreach ($languages as $language) {
                    if (isset($setting['img_block_titles'][$i][$language['language_id']])) {
                        $data['img_block_titles'][$i] = $setting['img_block_titles'][$i][$language['language_id']];
                    }
                    if ($language['language_id'] == $this->config->get('config_language_id')) {
                        break;
                    }
                }
            }
            // Load owl-carousel if is used in template
            if (strpos($setting['img_block_template'], 'owlcarousel') !== false ) {
                $this->document->addStyle('catalog/view/javascript/jquery/owl-carousel/owl.carousel.css');
                $this->document->addScript('catalog/view/javascript/jquery/owl-carousel/owl.carousel.min.js');
            }

            $data['content'] .= $this->load->view('extension/module/' . pathinfo($setting['img_block_template'], PATHINFO_FILENAME), $data);
        }

        // allows php content
        ob_start();
        eval("?>" . $data['content'] . "<?");
        $data['content'] = ob_get_contents();
        ob_end_clean();

        // Render main output
        return $this->load->view('extension/module/como_htmlcode', $data);
    }
}
