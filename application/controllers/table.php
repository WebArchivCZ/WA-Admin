<?php
/**
 * DONE prokliky z jednotlivych radku na prohlizeni konkretnich zaznamu
 */
abstract class Table_Controller extends Template_Controller
{
    protected $table;
    protected $title;
    protected $model;
    protected $view = 'table';

    public function __construct()
    {
        $this->model = inflector::singular($this->table);
        parent::__construct();
        $this->template->title = Kohana::lang('tables.'.$this->title);
    }

    public function index()
    {
        $per_page = $this->input->get('limit', 20);
        $page_num = $this->input->get('page', 1);
        $offset   = ($page_num - 1) * $per_page;

        $model = ORM::factory($this->model);
        $pages = Pagination::factory(array
            (
            'style' => 'dropdown',
            'items_per_page' => $per_page,
            'query_string' => 'page',
            'total_items' => $model->count_all(),

        ));

        $pages_inline = Pagination::factory(array
            (
            'style' => 'digg',
            'items_per_page' => $per_page,
            'query_string' => 'page',
            'total_items' => $model->count_all(),

        ));

        $view = new View($this->view);
        $view->title = $this->title;
        $view->headers = $model->headers;
        $view->columns = $model->table_columns();
        $view->items = $model->find_all($per_page,$offset);
        $view->pages = $pages . $pages_inline;
        $this->template->content = $view;
        $this->template->title = Kohana::lang('tables.'.$this->title) . " | " . Kohana::lang('tables.index');
    }

    public function view($id = FALSE) {
        $this->template->title = 'Zobrazení záznamu';
        $resource = ORM::factory($this->model, $id);
        $resource_values = $resource->as_array();
        $values = array();
        foreach ($resource_values as $key => $value) {
            if ($resource->__isset($key)) {
                // TODO elegantnejsi vypisovani cizich klicu
                    $key = str_replace('_id', '',$key);
                    $values[$key] = $resource->{$key};

            }
        }
        $url = url::site("/tables/{$this->table}/edit/{$id}");
        $this->template->content = View::factory('tables/record_view')
                                            ->bind('values', $values)
                                            ->set('edit_url', $url);
    }

    public function edit($id = FALSE)
    {
        $form = Formo::factory()->orm($this->model, $id)
                                ->add('submit', 'Upravit')
                                ->label_filter('display::translate_orm')
                                ->label_filter('ucfirst');
        $view = new View('edit_table');
        $view->type = 'edit';
        $view->form = $form->get();
        $this->template->content = $view;
        if ($form->validate())
        {
            $form->save();
            $this->session->set_flash('message', 'Zaznam uspesne zmenen');
        }
    }

    public function add()
    {
         $form = Formo::factory()->orm($this->model)->add('submit', 'Vlozit')->remove('id');
        // TODO vypisovani labelu
        $view = new View('edit_table');
        $view->type = 'add';
        $view->form = $form->get();
        $this->template->content = $view;
        if ($form->validate())
        {
            $form->save();
            $this->session->set_flash('message', 'Zaznam uspesne pridan');
        }
    }

    public function delete($id = FALSE)
    {
        $form = Formo::factory()->orm($this->model, $id)->add('submit', 'SMAZAT');
        // TODO vypisovani labelu
        $view = new View('edit_table');
        $view->type = 'delete';
        $view->form = $form->get();
        $this->template->content = $view;
        if ($form->validate())
        {
            ORM::factory($this->model)->delete($id);
            $this->session->set_flash('message', 'Zaznam uspesne smazan');
            url::redirect(url::site('/tables/'.$this->table));
        }
        
    }

    public function search()
    {
        $search_string = $this->input->post('search_string');

        $per_page = $this->input->get('limit', 20);
        $page_num = $this->input->get('page', 1);
        $offset   = ($page_num - 1) * $per_page;

        $model = ORM::factory($this->model);
        $result = $model->like($model->__get('primary_val'), $search_string)->find_all($per_page,$offset);
        $pages = Pagination::factory(array
            (
            'style' => 'dropdown',
            'items_per_page' => $per_page,
            'query_string' => 'page',
            'total_items' => $result->count(),

        ));

        $pages_inline = Pagination::factory(array
            (
            'style' => 'digg',
            'items_per_page' => $per_page,
            'query_string' => 'page',
            'total_items' => $result->count(),

        ));

        $view = new View('table');
        $view->title = $this->title;
        $view->headers = $model->headers;
        $view->columns = $model->table_columns();
        // TODO predefinovat hledani - prohledavane sloupce definovat v modelu
        $view->items = $result;
        $view->pages = $pages . $pages_inline;
        $this->template->content = $view;
        $this->template->title = Kohana::lang('tables.'.$this->title) . " | " . Kohana::lang('tables.index');
    }
}
?>