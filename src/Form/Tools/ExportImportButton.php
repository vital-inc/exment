<?php

namespace Exceedone\Exment\Form\Tools;

use Encore\Admin\Grid;
use Exceedone\Exment\Model\CustomTable;
use Exceedone\Exment\Model\Plugin;
use Exceedone\Exment\Enums\PluginType;

/**
 * Data export and import button
 */
class ExportImportButton extends ModalTileMenuButton
{
    protected $grid;
    protected $endpoint;
    protected $total_export_flg;
    protected $export_flg;
    protected $import_flg;
    protected $view_flg;

    /**
     * custom table if from custom value
     *
     * @var CustomTable
     */
    protected $custom_table;

    public function __construct($endpoint, Grid $grid, $view_flg = false, $export_flg = true, $import_flg = true)
    {
        $this->grid = $grid;
        $this->endpoint = $endpoint;
        $this->export_flg = !boolval(config('exment.export_disabled', false)) && $export_flg;
        $this->import_flg = !boolval(config('exment.import_disabled', false)) && $import_flg;
        $this->view_flg = !boolval(config('exment.export_view_disabled', false)) && $view_flg;
        
        // switch label
        $this->total_export_flg = $this->export_flg || $this->view_flg;

        if ($this->total_export_flg && $this->import_flg) {
            $label = exmtrans('custom_value.import_export');
        } elseif ($this->total_export_flg) {
            $label = exmtrans('custom_value.export');
        } elseif ($this->import_flg) {
            $label = exmtrans('custom_value.import_label');
        } else {
            $label = '';
        }

        parent::__construct([
            'label' => $label,
            'icon' => 'fa-download',
            'button_class' => 'btn-twitter',
        ]);
    }
    
    /**
     * Set parent grid.
     *
     * @param Grid $grid
     *
     * @return $this
     */
    public function setGrid(Grid $grid)
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * Set custom table.
     *
     * @param Grid $grid
     *
     * @return $this
     */
    public function setCustomTable(CustomTable $custom_table)
    {
        $this->custom_table = $custom_table;

        return $this;
    }

    /**
     * @return Grid
     */
    public function getGrid()
    {
        return $this->grid;
    }

    /**
     * Render Export button.
     *
     * @return string
     */
    public function render()
    {
        if ($this->disabledButton()) {
            return;
        }

        $page = request('page', 1);

        $export = trans('admin.export');
        $all = trans('admin.all');
        $currentPage = trans('admin.current_page');

        $groups = [];

        // output formats
        $formats = [];
        // check config value
        if (!boolval(config('exment.export_import_export_disabled_csv', false))) {
            $formats['csv'] = [
                'label' => 'CSV',
                'icon' => 'fa-file-o',
                'attributes' => formatAttributes([
                    'target' => '_blank',
                ]),
            ];
        }
        if (!boolval(config('exment.export_import_export_disabled_excel', false))) {
            $formats['excel'] = [
                'label' => 'Excel',
                'icon' => 'fa-file-excel-o',
                'attributes' => formatAttributes([
                    'target' => '_blank',
                ]),
            ];
        }

        ///// Append default output
        if($this->export_flg){
            $groups[] = [
                'header' => exmtrans('custom_value.default_export'),
                'items' => [
                    [
                        'icon' => 'fa-table',
                        'header' => $all,
                        'description' => exmtrans('custom_value.help.export_all'),
                        'buttons' => collect($formats)->map(function($format, $key){
                            return array_merge(['href'=> $this->grid->getExportUrl('all') . "&action=export&format=$key"], $format);
                        })->toArray(),
                    ],
                    [
                        'icon' => 'fa-table',
                        'header' => $currentPage,
                        'description' => exmtrans('custom_value.help.export_page'),
                        'buttons' => collect($formats)->map(function($format, $key) use($page){
                            return array_merge(['href'=> $this->grid->getExportUrl('page', $page) . "&action=export&format=$key"], $format);
                        })->toArray(),
                    ],
                ]
            ];
        }

        ///// Append view output
        if($this->view_flg){
            $groups[] = [
                'header' => exmtrans('custom_value.view_export'),
                'items' => [
                    [
                        'icon' => 'fa-th-list',
                        'header' => $all,
                        'description' => exmtrans('custom_value.help.view_export_all'),
                        'buttons' => collect($formats)->map(function($format, $key){
                            return array_merge(['href'=> $this->grid->getExportUrl('all') . "&action=view_export&format=$key"], $format);
                        })->toArray(),
                    ],
                    [
                        'icon' => 'fa-th-list',
                        'header' => $currentPage,
                        'description' => exmtrans('custom_value.help.view_export_page'),
                        'buttons' => collect($formats)->map(function($format, $key) use($page){
                            return array_merge(['href'=> $this->grid->getExportUrl('page', $page) . "&action=view_export&format=$key"], $format);
                        })->toArray(),
                    ],
                ]
            ];
        }

        $plugins = $this->getPluginExports();
        if(!is_nullorempty($plugins)){
            foreach($plugins as $plugin){
                $button = [
                    'label' => exmtrans('custom_value.export'),
                    'icon' => 'fa-file-o',
                    'attributes' => formatAttributes([
                        'target' => '_blank',
                    ]),
                ];

                $items = [];
                // export_types
                $export_types = stringToArray($plugin->getOption('export_types', ['all', 'current_page']));

                if(in_array('all', $export_types)){
                    $items[] = [
                        'icon' => $plugin->getOption('icon') ?? 'fa-th-list',
                        'header' => $all,
                        'description' => $plugin->getOption('export_description'),
                        'buttons' => [array_merge(['href'=> $this->grid->getExportUrl('all') . "&action=plugin_export&plugin_id={$plugin->id}"], $button)],
                    ];
                }
                if(in_array('current_page', $export_types)){
                    $items[] = [
                        'icon' => $plugin->getOption('icon') ?? 'fa-th-list',
                        'header' => $currentPage,
                        'description' => $plugin->getOption('export_description'),
                        'buttons' => [array_merge(['href'=> $this->grid->getExportUrl('page', $page) . "&action=plugin_export&plugin_id={$plugin->id}"], $button)],
                    ];
                }

                $groups[] = [
                    'header' => $plugin->getOption('label') ?? $plugin->plugin_view_name,
                    'items' => $items,
                ];
            }
        }

        if ($this->import_flg) {
            $groups[] = [
                'header' => exmtrans('common.import'),
                'items' => [
                    [
                        'icon' => 'fa-download',
                        'header' => exmtrans('custom_value.template'),
                        'description' => exmtrans('custom_value.help.template'),
                        'buttons' => collect($formats)->map(function($format, $key){
                            return array_merge(['href'=> $this->endpoint."?_export_=all&temp=1&format=$key"], $format);
                        })->toArray(),
                    ],
                    [
                        'icon' => 'fa-upload',
                        'header' => exmtrans('common.import'),
                        'description' => exmtrans('custom_value.help.import'),
                        'buttons' => [
                            [
                                'label' => exmtrans('common.import'),
                                'icon' => 'fa-upload',
                                'url' => '#',
                                'attributes' => formatAttributes([
                                    'data-widgetmodal_url' => url_join($this->endpoint, 'importModal')
                                ]),
                            ]
                        ],
                    ],
                ]
            ];
        }

        $this->groups = $groups;
        $this->modal_title = $this->label;

        return parent::render();
    }

    protected function disabledButton()
    {
        if (boolval(config('exment.export_view_disabled', false)) && boolval(config('exment.export_disabled', false)) && boolval(config('exment.import_disabled', false))) {
            return true;
        }

        if (boolval(config('exment.export_import_export_disabled_csv', false)) && boolval(config('exment.export_import_export_disabled_excel', false))) {
            return true;
        }

        return false;
    }

    protected function getPluginExports(){
        if(!isset($this->custom_table)){
            return collect();
        }

        return Plugin::getPluginsByTable($this->custom_table)->filter(function($plugin){
            return $plugin->matchPluginType(PluginType::EXPORT);
        });
    }
}
