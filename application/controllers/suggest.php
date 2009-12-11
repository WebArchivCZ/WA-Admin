<?php
class Suggest_Controller extends Template_Controller
{

    protected $title = 'Vložení nového zdroje';

    public function index ()
    {
        $view = View::factory('form');

        $form = Formo::factory('add_form');
        $form->add('title')->label('Název');
        $form->add('publisher')->label('Vydavatel');
        $form->add('url')->label('URL')->add_rule('url', 'valid_custom::simple_domain', 'Zadejte co nejkratší doménu (domena.cz).');
        ;
        $form->add_rules('required', 'title|publisher|url', 'Povinná položka');
        $form->add('submit', 'Ověřit');

        $view->form = $form;
        $view->header = 'Ověřit vkládaný zdroj';

        if ($form->validate())
        {
            $publisher_name = $form->publisher->value;
            $title = $form->title->value;
            $url = $form->url->value;

            // nastaveni promennych pro vyuziti v metode insert()
            $resource_array = array('title' => $title,
                    'url' => $url,
                    'publisher' => $publisher_name);
            $this->session->set('resource_val', $resource_array);

            $resources = $this->check_records($publisher_name, $title, $url);
            if ($resources->count() == 0)
            {
                $this->session->set_flash('message', 'Nebyly nalezeny shody.');
                url::redirect('suggest/insert/');
            } else
            {
                $view = View::factory('match_resources');

                $view->match_resources = $resources;
                $this->help_box = 'Kliknutím na konkrétního vydavatele
                přiřadíte již existujícího vydavatele nově vkládanému zdroji';
            }
        } else
        {
            $view->form = $form->get();
        }

        $this->template->content = $view;
    }

    public function insert ($publisher_id = NULL)
    {
        // nacteni hodnot z predchoziho formulare
        $resource_val = $this->session->get('resource_val');

        $title = $resource_val['title'];
        $url = $resource_val['url'];

        if ($publisher_id != NULL AND is_numeric($publisher_id))
        {
            $publisher = ORM::factory('publisher', $publisher_id);
            $publisher_name = $publisher->name;
        } else
        {
            $publisher_name = $resource_val['publisher'];
        }

        $curators = ORM::factory('curator')->where('active', 1)->select_list('id', 'vocative');
        $conspectus = ORM::factory('conspectus')->select_list('id', 'category');
        // TODO mozno presunout do select_list() v ConspectusModel
        foreach ($conspectus as $id => $category)
        {
            $conspectus[$id] = $id . ' - ' . $category;
        }

        $suggested_by = ORM::factory('suggested_by')->select_list('id', 'proposer');

        $curator_id = Auth::instance()->get_user()->id;

        $form = Formo::factory('insert_resource');
        $form->add('title')->label('Název zdroje')->value($title);
        $form->add('url')->label('URL')->value($url)->add_rule('url', 'url', 'Musí být ve tvaru url.');
        $form->add('publisher')->label('Vydavatel')->value($publisher_name);
        $form->add_select('curator', $curators)->label('Kurátor')->value($curator_id);
        $form->add_select('conspectus', $conspectus)->label('Konspekt');
        $form->add_select('suggested_by', $suggested_by)->label('Navrhl');
        $form->add('submit', 'Vložit zdroj');

        if(isset($publisher) AND $publisher->id != '')
        {
            $form->publisher->readonly(TRUE);
        }

        if ($form->validate())
        {
            $publisher_name = $form->publisher->value;

            $publisher = ORM::factory('publisher');
            if (isset($publisher_id))
            {
                $publisher->find($publisher_id);
                
            }
            if (! $publisher->loaded)
            {
                $publisher->name = $publisher_name;
                $publisher->save();
            }

            $url = $form->url->value;

            // vytvoreni zdroje a jeho ulozeni
            $resource = ORM::factory('resource');
            $resource->title = $form->title->value;
            $resource->url = $url;
            $resource->publisher_id = $publisher->id;
            $resource->conspectus_id = $form->conspectus->value;
            $resource->creator_id = $curator_id;
            $resource->curator_id = $form->curator->value;
            $resource->suggested_by_id = $form->suggested_by->value;
            $resource->resource_status_id = RS_NEW;
            $resource->save();

            // vytvoreni seminka a jeho ulozeni
            $seed = ORM::factory('seed');
            $seed->url = $url;
            $seed->resource_id = $resource->id;
            $seed->seed_status_id = ORM::factory('seed_status', 1)->id;
            $seed->valid_from = date('Y-m-d');

            $seed->save();

            $this->session->delete('resource_val');

            // presmerujeme na hodnoceni
            url::redirect('rate');
        } else
        {
            $this->template->content = View::factory('form')
                    ->bind('form', $form)
                    ->set('header', 'Vložit zdroj');
        }
    }
    /**
     * Vyhleda zdroje a vydavatele vyhovujici zadanym podminkam a vrati je jako iterator
     * @param String $publisher_name jmeno vydavatele
     * @param String $title nazev zdroje
     * @param String $url url zdroje
     * @return Resource_Iterator mnozina vyhovujicich zdroju
     */
    private function check_records ($publisher_name, $title, $url)
    {
        $resources = ORM::factory('resource')
                ->join('publishers', 'resources.publisher_id = publishers.id')
                ->orlike(array('url' => $url , 'title' => $title, 'publishers.name'=>$publisher_name))
                ->find_all();

        return $resources;
    }

}
?>