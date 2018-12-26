<?php

namespace Exceedone\Exment\Model;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Exceedone\Exment\Enums\AuthorityValue;
use Exceedone\Exment\Enums\SystemTableName;

class CustomValueModelScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $table_name = $model->custom_table->table_name;
        // get user info
        $user = \Exment::user();
        // if not have, check as login
        if(!isset($user)){
            // no access authority
            //throw new \Exception;
            
            // set no filter. Because when this function called, almost after login or pass oauth authonize.
            // if throw exception, Cannot execute batch.
            return;
        }

        // if user can access list, return
        if($table_name == SystemTableName::USER){
            //TODO
            return;
        }
        elseif($table_name == SystemTableName::ORGANIZATION){
            //TODO
            return;
        }
        elseif ($model->custom_table->hasPermission(AuthorityValue::AVAILABLE_ALL_CUSTOM_VALUE)) {
            return;
        }
        // if user has edit or view table
        elseif ($model->custom_table->hasPermission(AuthorityValue::AVAILABLE_ACCESS_CUSTOM_VALUE)) {
            // get only has authority
            $builder
                ->whereHas('value_authoritable_users', function ($q) use($user) {
                    $q->where('related_id', $user->base_user_id);
                })->orWhereHas('value_authoritable_organizations', function ($q) use($user) {
                    $q->whereIn('related_id', $user->getOrganizationIds());
                });
        }
        // if not authority, set always false result. 
        else{
            $builder->where('id','<', 0);
        }
    }
}