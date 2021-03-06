
namespace Exment {
    /**
    * Column Column Script.
    */
    export class CustomColumnEvent {
        public static AddEvent() {
        }

        public static AddEventOnce() {
            $(document).off('click', '[data-contentname="options_calc_formula"] .button-addcalcitem').on('click', '[data-contentname="options_calc_formula"] .button-addcalcitem', {}, CustomColumnEvent.calcButtonAddItemEvent);
            $(document).off('click', '[data-contentname="options_calc_formula"] .col-value-item-remove').on('click', '[data-contentname="options_calc_formula"] .col-value-item-remove', {}, CustomColumnEvent.calcRemoveItemEvent);

            $(document).on('pjax:complete', function (event) {
                CustomColumnEvent.AddEvent();
            });
        }

        private static calcButtonAddItemEvent = (ev) => {
            var target = $(ev.target).closest('.button-addcalcitem');
            // get target type
            var type = target.data('type');
        
            // get template
            var template:any = document.querySelector('.col-value-template');
            // create clone
            var clone = document.importNode(template.content, true);
            clone.querySelector('.col-value-item').dataset.type = type;
            ///// switch using type
            switch(type){
                case 'dynamic':
                case 'select_table':
                case 'symbol':
                    // set data-val and text
                    clone.querySelector('.col-value-item').dataset.val = target.data('val');
                    if(hasValue(target.data('from'))){
                        clone.querySelector('.col-value-item').dataset.from = target.data('from');
                    }
                    clone.querySelector('span').textContent = target.text();
                    break;
                case 'summary':
                case 'count':
                    // set data-val and text
                    clone.querySelector('.col-value-item').dataset.val = target.data('val');
                    if(hasValue(target.data('table'))){
                        clone.querySelector('.col-value-item').dataset.table = target.data('table');
                    }
                    clone.querySelector('span').textContent = target.text();
                    break;
                case 'fixed':
                    // set data-val from col-target-fixedval
                    var fixedval = target.closest('.row').find('.col-target-fixedval').val();
                    if(!hasValue(fixedval)){return;}
                    clone.querySelector('.col-value-item').dataset.val = fixedval;
                    clone.querySelector('span').textContent = fixedval;
                    break;
            }
        
            // set item
            $('.calc_formula_area').append(clone);
        }

        public static GetSettingValText(){
            // get col value item list
            let values = $('.calc_formula_area').find('.col-value-item');
            // get items and texts
            let items = [];
            let texts = [];
            for(var i = 0; i < values.length; i++){
                // get value
                let val = values.eq(i);
                // push value
                let itemval = {'type':val.data('type'), 'val': val.data('val')};
                if(hasValue(val.data('from'))){
                    itemval['from'] = val.data('from');
                }
                if(hasValue(val.data('table'))){
                    itemval['table'] = val.data('table');
                }
                items.push(itemval);

                // push text
                texts.push(escHtml(val.text()));
            }

            return {value: JSON.stringify(items), text: texts.join(' ')};
        }

        private static calcRemoveItemEvent = (ev) => {
            // remove item
            $(ev.target).closest('.col-value-item').remove();
        }
    }
}
$(function () {
    Exment.CustomColumnEvent.AddEvent();
    Exment.CustomColumnEvent.AddEventOnce();
});
