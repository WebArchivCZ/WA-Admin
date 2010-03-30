<?php
class Quality_Control_Controller extends Template_Controller {

    protected $title = 'Kontrola kvality';

    public function index() {
        $view =	new View('quality_control');

        $curator_id = $this->user->id;

        $resources_to_control = Resource_Model::get_to_checkQA($curator_id);
        $qa_checks_unsatisfactory = Qa_Check_Model::get_checks(-1, $curator_id);
        $view->resources_to_control = $resources_to_control;
        $view->qa_checks_unsatisfactory = $qa_checks_unsatisfactory;
        $this->template->content = $view;
    }

    public function add($resource_id = NULL) {
        if (is_null($resource_id)) {
            message::set_flash('Není nastaveno id zdroje.');
            url::redirect('quality_control');
        }

        $resource = ORM::factory('resource', $resource_id);
        $bool_values = array('TRUE'=>'Ano', 'FALSE'=>'Ne');
        $check_result_values = Qa_Check_Model::get_result_array();
        $wayback_url = Kohana::config('wadmin.wayback_url').$resource->url;

        $problems = ORM::factory('qa_problem')->find_all();
        
        $resource_url = html::anchor(url::site('/tables/resources/view/'.$resource->id), $resource->title);

        $form = Formo::factory('qa_form');
        $form->add_html("<p><label>Název:</label>{$resource_url}</p>");
        $form->add_html("<p><label>URL:</label>".html::anchor($resource->url, $resource->url, array('target'=>'_blank'))."</p>");
        $form->add_html("<p><label>Wayback:</label>".html::anchor($wayback_url, 'otevřít wayback', array('target'=>'_blank'))."</p>");
        $form->add('date_crawled')->label('Sklizeno dne');
        $form->add('ticket_no')->label('Číslo ticketu');
        foreach ($problems as $problem) {
            $form->add_group($problem->problem, $bool_values)
                    ->label($problem->question)
                    ->check($problem->problem, 'ano');
            $form->{$problem->problem}->group_open = '<span class="problem">';

            $problem_url_input = $problem->problem.'_url';
            $form->add($problem_url_input)
             	 ->label('URL problému');
            $form->{$problem_url_input}->open = '<p class="hidden" id="'.$problem_url_input.'">';
             	 
            $problem_comments_input = $problem->problem.'_comments';
            $form->add('textarea', $problem_comments_input)
            	 ->label('Komentář problému');
            $form->{$problem_comments_input}->open = '<p class="hidden" id="'.$problem_comments_input.'">';
        }
		$form->add_group('proxy_fine', $bool_values)
			 ->label('V proxy je vše v pořádku')
			 ->check('ano');
		$form->add('textarea', 'proxy_problems')
			 ->label('Problémy v proxy')
			 ->set('proxy_problems', 'open', '<p class="hidden" id="proxy_comments">');
			 
        $form->add_select('result', $check_result_values)
                ->label('Výsledek kontroly');

        $form->add('textarea', 'comments')->label('Komentář');
        $form->add('submit', 'save')->value('Uložit');

        if ($form->validate()) {
            $is_saved = $this->save($form, $resource_id);
            if ($is_saved) {
                message::set_flash('Kontrola byla úspěšně uložena.');
            } else {
                message::set_flash('Vyskytl se problém a kontrola nebyla uložena.');
            }
            url::redirect('quality_control');
        } else {
            $view = View::factory('forms/quality_control_form');
            $view->form = $form;
            $view->resource = $resource;

            $this->template->content = $view;
        }
    }

    public function view ($id) {
        $this->template->title = 'Zobrazení záznamu';
        $this->view = 'tables/record_qa_view';

        $record = ORM::factory('qa_check', $id);
        

        $record_values = $record->as_array();
        $values = array();
        foreach ($record_values as $key => $value) {
            if ($record->__isset($key)) {
                // TODO elegantnejsi vypisovani cizich klicu
                if ($record->is_related(str_replace('_id', '', $key))) {
                    $key = str_replace('_id', '',$key);
                }
                $values[$key] = $record->{$key};

            }
        }

        $url = url::site("/quality_control/edit/{$id}");
        $view = View::factory($this->view);
        $view->record = $record;
        $view->bind('values', $values);
        $view->set('header', "Zobrazení kontroly kvality");
        $view->set('edit_url', $url);
        $this->template->content = $view;
    }

    private function save($form, $resource_id) {
        $qa_check = ORM::factory('qa_check');

        $qa_check->resource_id = $resource_id;
        $qa_check->date_checked = date('Y-m-d h:i:s');
        $qa_check->date_crawled = $form->date_crawled->value;
        $qa_check->ticket_no = $form->ticket_no->value;

        $problems = ORM::factory('qa_problem')->find_all();

        $qa_check->result = $form->result->value;
        $qa_check->comments = $form->comments->value;
        $qa_check->add_curator(Auth::instance()->get_user());
        
        $qa_check->proxy_problems = $form->proxy_problems->value;

        $qa_check->save();
        
    	foreach ($problems as $problem) {
            // pokud je FALSE pridame problem
            if ($form->{$problem->problem}->value == 'FALSE') {
                $qa_check_problem = ORM::factory('qa_check_problem');
                $qa_check_problem->qa_check_id = $qa_check->id;
                $qa_check_problem->qa_problem_id = $problem->id;
                $qa_check_problem->url = $form->{$problem->problem."_url"}->value;
                $qa_check_problem->comments = $form->{$problem->problem."_comments"}->value;
                $qa_check_problem->save();
                if ($qa_check_problem->saved == FALSE) {
                	return FALSE;
                }
            }
        }
        
        if ($qa_check->saved == TRUE) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

}
?>
