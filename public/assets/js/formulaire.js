var generated_id = [];
const sections_panel = '.sections_panel';
const sections_panel2 = '.sections_panel2';
const sections_panel3 = '.paragraph_item3';
var type_champ_select_dropdown = '';
var type_param_select_dropdown = '';

function exist_in_arr(arr, obj) {
    for (var i = 0; i < arr.length; i++) {
        if (arr[i] == obj) return true;
    }
    return false;
}

function randomId(min, max) {
    min = 0;
    max = 9999;

    var random = (Math.random() * (max - min) + min) * 100000;
    if(!exist_in_arr(generated_id , random)){
        generated_id.push(parseInt(random));
        return parseInt(random);

    }
    else{
        return randomId();
    }


}

$(document).ready(function (){
    const type_champ_select_tag = '.type_champ_select_tag';
    const type_param_select_tag = '.type_param_select_tag';

    type_champ_select_dropdown = $(type_champ_select_tag).html();
    $(type_champ_select_tag).remove();

    type_param_select_dropdown = $(type_param_select_tag).html();
    $(type_param_select_tag).remove();


    //auto select selected select
    const selects = $('select');
    for(var i = 0; i< selects.length ; i++){
        const select = selects[i];
        if(has_attr($(select) , 'value')){
            $(select).val($(select).attr('value'));
        }
    }
});

$(document).on('click','.opener',{passive:true},function () {
    const tag = $(this);

    $(tag).toggleClass('icon-arrow-up')
});

$(document).on('click','.delete_item',{passive:true},function () {
    const tag = $($(this).attr('data-target'));
    var message = $(this).attr('data-message');
    message = message || 'cet élément'
    function delete_section(){
        remove_tag(tag);
        show_message('info' , 'élément supprimer avec succès')
    }
    sweetConfirm('Être vous certains de vouloir supprimer '+message+' ?' , {yes : delete_section});
});

//section
$(document).on('click','.add_section',{passive:true},function () {
    const section_id = randomId();
    const accordeon_id = randomId();

    const html = '<div id="section_'+section_id+'" class="card section_item" data-children=".item">'+
        '    <div class="card-header card-header-primary ">'+
        '        <div class="form-row">'+
        '            <a type="button" data-toggle="collapse" data-parent="#section_'+section_id+'" href="#accordion_'+accordeon_id+'" class="collapsed icon-arrow-down col-sm-1 opener text-center mt-2"></a>'+
        '            <div class="col-sm-8">'+
        '                <label>Titre de la Section :</label><input type="text" name="titre_section" placeholder="titre de la section" class="form-control" >'+
        '            </div>'+
        '            <div class="col-sm-3 text-right">'+
        '                <div class="btn-group">'+
        '                    <button class="btn add_sub_section" data-id="'+section_id+'" data-toggle="tooltip" data-placement="top" title="Ajouter une sous section" type="button"><i class="icon-add-more"></i></button>' +
        '                    <button class="btn delete_item btn-link text-danger" data-target="#section_'+section_id+'" data-message="cette section et son contenue" data-toggle="tooltip" data-placement="top" title="Supprimer cette section" type="button"><i class="icon-delete"></i></button>' +
        '                </div>'+
        '            </div>'+
        '        </div>'+
        '    </div>'+
        '    <div class="item">'+
        '        <div id="accordion_'+accordeon_id+'" class="collapse" role="tabpanel">'+
        '            <div class="card-body section_content">'+
        '            </div>'+
        '        </div>'+
        '    </div>'+
        '</div>';

    $(sections_panel).append(html);

    show_message('success' , 'Section ajouter avec succès')
    setTimeout(function(){
        enable_tooltip();
        $('.add_sub_section[data-id="'+section_id+'"]').click();
    },300);
});

//sub_sections
$(document).on('click','.add_sub_section',{passive:true},function () {
    const tag = $(this);
    const sub_section_id = randomId();
    const sub_accordeon_id = randomId();
    const section_id = $(this).attr('data-id');
    const section_content = '#section_'+section_id+' .section_content';

    const html = '<div id="sub_section_'+sub_section_id+'" class="card sub_section_item" data-children=".item">'+
        '    <div class="card-header card-header-primary ">'+
        '        <div class="form-row">'+
        '            <a data-toggle="collapse" data-parent="#sub_section_'+sub_section_id+'" href="#sub_accordion_'+sub_accordeon_id+'" class="collapsed icon-arrow-down col-sm-1 opener text-center mt-2"></a>'+
        '            <div class="col-sm-8">'+
        '                <label>Titre de la Sous-Section :</label><input type="text" name="titre_sous_section" placeholder="titre de la sous-section" class="form-control" >'+
        '            </div>'+
        '            <div class="col-sm-3 text-right">'+
        '                <div class="btn-group">'+
        '                    <button class="btn add_paragraph" data-id="'+sub_section_id+'" data-toggle="tooltip" data-placement="top" title="Ajouter un Paragraphe" type="button"><i class="icon-add-more"></i></button>' +
        '                    <button class="btn delete_item btn-link text-danger" data-target="#sub_section_'+sub_section_id+'" data-message="cette sous-section et son contenue" data-toggle="tooltip" data-placement="top" title="Supprimer sous-section" type="button"><i class="icon-delete"></i></button>' +
        '                </div>'+
        '            </div>'+
        '        </div>'+
        '    </div>'+
        '    <div class="item">'+
        '        <div id="sub_accordion_'+sub_accordeon_id+'" class="collapse" role="tabpanel">'+
        '            <div class="card-body sub_section_content">'+
        '            </div>'+
        '        </div>'+
        '    </div>'+
        '</div>';

    $(section_content).append(html);
    show_message('success' , 'Sous-section ajouter avec succès')
    setTimeout(function(){
        $('.add_paragraph[data-id="'+sub_section_id+'"]').click();
        enable_tooltip()
    },300);
});

//paragraphe
$(document).on('click','.add_paragraph',{passive:true},function () {
    const tag = $(this);
    const paragraph_id = randomId();
    const accordion_paragraph_id = randomId();
    const sub_section_id = $(this).attr('data-id');
    const sub_section_content = '#sub_section_'+sub_section_id+' .sub_section_content';
    const html = '<div id="paragraph_'+paragraph_id+'" class="card paragraph_item" data-children=".item">'+
        '    <div class="card-header card-header-primary ">'+
        '        <div class="form-row">'+
        '            <a data-toggle="collapse" data-parent="#paragraph_'+paragraph_id+'" href="#sub_accordion_para_'+accordion_paragraph_id+'" class="collapsed icon-arrow-down col-sm-1 opener text-center mt-2"></a>'+
        '            <div class="col-sm-8">'+
        '                <label>Titre du Paragraphe :</label> <input type="text" name="titre_pg" placeholder="titre du Paragraphe" class="form-control">'+
        '            </div>'+
        '            <div class="col-sm-3 text-right">'+
        '                <div class="btn-group">'+
        '                <div class="btn-group">'+
        '                    <button class="btn add_question" data-id="'+paragraph_id+'" data-toggle="tooltip" data-placement="top" title="Ajouter une Question" type="button"><i class="icon-add-more"></i></button>' +
        '                    <button class="btn delete_item btn-link text-danger" data-target="#paragraph_'+paragraph_id+'" data-message="ce paragraphe et son contenue" data-toggle="tooltip" data-placement="top" title="Supprimer ce paragraphe" type="button"><i class="icon-delete"></i></button>' +
        '                </div>'+
        '                </div>'+
        '            </div>'+
        '        </div>'+
        '    </div>'+
        '    <div class="item">'+
        '        <div id="sub_accordion_para_'+accordion_paragraph_id+'" class="collapse" role="tabpanel">'+
        '            <div class="card-body paragraph_content form-row">'+
        '            </div>'+
        '        </div>'+
        '    </div>'+
        '</div>';

    $(sub_section_content).append(html);
    show_message('info' , 'Paragraphe ajouter avec succès')

    setTimeout(function(){
        $('.add_question[data-id="'+paragraph_id+'"]').click();
        enable_tooltip();
    },300);

});

//question
$(document).on('click','.selectType',{passive:true},function () {
    const question_id = $(this).attr('data-question_id');
    const target = $('#question_item_'+question_id+' '+$(this).attr('data-cible-type'));

    $('#question_item_'+question_id+'').find('.type_item').hide('slow');
    $(target).show('slow');
});

$(document).on('change','.typequestion',{passive:true},function () {

    const value = $(this).val();
    console.log(value);
    const parent = $(this).parents('.type_champ_select_indicator');
    console.log(parent);
    const question_id = $(parent).attr('data-question_id');
    console.log(question_id);
    const target = $('#question_item_'+question_id+' '+$(parent).attr('data-textarea-cible'));

    if (value == "select" || value =="checkbox" || value =="radio"){
        $(target).show('slow');
    }
    else {
        $(target).hide('slow');

    }


});

$(document).on('click','.add_question',{passive:true},function () {

    const tag = $(this);
    const question_id = randomId();
    const paragraph_id = $(this).attr('data-id');
    const paragraph_content = '#paragraph_'+paragraph_id+' .paragraph_content';

    const qst_number = $(paragraph_content).find('.question_item').length+1;

    const html =  '<div class="col-sm-4 question_item" id="question_item_'+question_id+'">'+
        '    <div class="card">'+
        '        <div class="card-header card-header-primary">' +
        '           <div class="form-row">' +
        '            <div class="col-sm-12">'+
        '                <span>Question  '+qst_number+'</span>'+
        '                <button type="button" class="btn btn-link float-right delete_item" data-target="#question_item_'+question_id+'"><i class="icon-delete text-danger"></i></button>'+
        '            </div>'+
        '           </div>       ' +
        '         </div>' +
        '        <div class="card-body">'+
        '            <div class="form-group">'+
        '                <div class="form-row border-bottom">'+
        '                    <div class="col-sm-6">'+
        '                            <label>Mandatory</label>'+
        '                    </div>'+
        '                    <div class="col-sm-2">'+
        '                        <div class="custom-control custom-radio">'+
        '                            <input class="custom-control-input" type="radio" name="mandatory_'+question_id+'" value="oui" data-question_id="'+question_id+'" id="mandatory_oui_'+question_id+'" checked>'+
        '                            <label class="custom-control-label" for="mandatory_oui_'+question_id+'"><small>Oui</small></label>'+
        '                        </div>'+
        '                    </div>'+
        '                    <div class="col-sm-2">'+
        '                        <div class="custom-control custom-radio">'+
        '                            <input class="custom-control-input" type="radio" name="mandatory_'+question_id+'" value="non" data-question_id="'+question_id+'" id="mandatory_non_'+question_id+'" >'+
        '                            <label class="custom-control-label" for="mandatory_non_'+question_id+'"><small>Non</small></label>'+
        '                        </div>'+
        '                    </div>'+
        '                </div>'+
        '                <div class="form-row" style="margin-top:35px">'+
        '                    <div class="col-sm-6">'+
        '                         <label>Appel d\'API</label>'+
        '                    </div>'+
        '                    <div class="col-sm-2">'+
        '                        <div class="custom-control custom-radio">'+
        '                            <input class="custom-control-input" type="radio" name="api_'+question_id+'" value="oui" data-question_id="'+question_id+'" id="api_oui_'+question_id+'">'+
        '                            <label class="custom-control-label" for="api_oui_'+question_id+'"><small>Oui</small></label>'+
        '                        </div>'+
        '                    </div>'+
        '                    <div class="col-sm-2">'+
        '                        <div class="custom-control custom-radio">'+
        '                            <input class="custom-control-input" type="radio" name="api_'+question_id+'" value="non" data-question_id="'+question_id+'" id="api_non_'+question_id+'" checked>'+
        '                            <label class="custom-control-label" for="api_non_'+question_id+'"><small>Non</small></label>'+
        '                        </div>'+
        '                    </div>'+
        '                </div>'+
        '                <div class="form-row" style="margin-top:35px">'+
        '                    <div class="col-sm-6">'+
        '                        <div class="custom-control custom-radio">'+
        '                            <input class="custom-control-input selectType" type="radio" name="qst_type_'+question_id+'" data-question_id="'+question_id+'" id="qst_type_champ_'+question_id+'" data-cible-type="#type_champ_select_'+question_id+'" data-start-type="#type_param_select_'+question_id+'" checked>'+
        '                            <label class="custom-control-label" for="qst_type_champ_'+question_id+'"><small>Ajouter une question</small></label>'+
        '                        </div>'+
        '                    </div>'+
        '                    <div class="col-sm-6">'+
        '                        <div class="custom-control custom-radio">'+
        '                            <input class="custom-control-input selectType" type="radio" name="qst_type_'+question_id+'" data-question_id="'+question_id+'" id="qst_type_param_'+question_id+'" data-cible-type="#type_param_select_'+question_id+'" data-start-type="#type_champ_select_'+question_id+'">'+
        '                            <label class="custom-control-label" for="qst_type_param_'+question_id+'"><small>Sélectionner une question</small></label>'+
        '                        </div>'+
        '                    </div>'+
        '                </div>'+
        '                <div class="form-row select_type_'+question_id+'">'+
        '                    <div class="col-sm-12">'+
        '                        <div class="type_item type_champ_select_indicator" data-question_id="'+question_id+'" id="type_champ_select_'+question_id+'" data-textarea-cible="#question_textarea_'+question_id+'">'+
        '                           <div class="form-group">'+
        '                               <label>Nom de la question</label>'+
        '                               <input type="text" class="form-control" name="nom_question" required>'+
        '                           </div>'+
        '                           <div class="form-group">'+
        '                               <label class="">Type du champ</label>' +
                                            type_champ_select_dropdown+
        '                               <div class="mt-3" id="question_textarea_'+question_id+'" style="display: none">'+
        '                                   <textarea name="liste_option" rows="5" class="form-control" placeholder="Renseigner les differentes options ici, séparées de point virgules(;)"></textarea>'+
        '                               </div>'+
        '                           </div>'+
        '                        </div>'+
        '                        <div class="type_item" id="type_param_select_'+question_id+'" style="display: none">'+
        '                            <label class="">Question</label>'+
                                        type_param_select_dropdown+
        '                        </div>'+
        '                    </div>'+
        '                </div>'+
        '            </div>'+
        '        </div>'+
        '    </div>'+
        '</div>';
    show_message('warning' , 'Question ajouter avec succès');
    $(paragraph_content).append(html);
    setTimeout(function(){
        enable_tooltip();
    },300);

});

//submit option
$(document).on('click','#submit_form_btn',{passive:true},function () {

    if(validate_form('formulaire')){

        var form_json = {}
        form_json.titre = $('#form_title').val();
        form_json.typeVisiteId = $('#typevisite').val();
        form_json.sections = build_section();

        $('#form_json_container').val(JSON.stringify(form_json));

        setTimeout(function(){
            $('#formulaire').submit();
        },300);
    }
});

$(document).on('click','#submit_form_btn2',{passive:true},function () {

    if(validate_form('formulaire2')){

        var form_json = {}
        form_json.titre = $('#form_title2').val();
        form_json.typeVisiteId = $('#typevisite2').val();
        form_json.sections = build_section2();
//console.log(JSON.stringify(form_json));
        $('#form_json_container2').val(JSON.stringify(form_json));

        setTimeout(function(){
            $('#formulaire2').submit();
        },300);
    }
});

$(document).on('click','#submit_form_btn3',{passive:true},function () {

    if(validate_form('formulaire3')){

        var form_json = {}
        form_json.code = $('#form_title3').val();
        form_json.dysfonctionnement = $('#titre_pg3').val();
        form_json.questions = build_question3();

        $('#form_json_container3').val(JSON.stringify(form_json));

        setTimeout(function(){
            $('#formulaire3').submit();
        },300);
    }
});
////////////////////////////

//for input and select
$(document).on('keyup blur change','[type="text"] , select',{passive:true},function () {
    $(this).attr('value' , $(this).val());
});

//for textarea
$(document).on('keyup blur change','textarea',{passive:true},function () {
    $(this).html($(this).val());
});
//for radio and checkbox
$(document).on('click','[type="radio"] , [type="checkbox"]',{passive:true},function () {
   const tag = $(this);

   
   setTimeout(function(){
       const checked = $(tag).prop('checked');

       if($(tag).attr('type') == 'radio'){
           const name = $(tag).attr('name');
           $('[name="'+name+'"]').removeAttr('checked');
       }

       if(checked){
           $(tag).attr('checked','checked');
       }
       else{
           $(tag).removeAttr('checked');
       }
   },500);
});



function build_section(){
    var result_json = [];

    var tags = $('.section_item');
    for(var i = 0; i<tags.length ; i++){
        const content  = tags[i];
        const content_tag = '#'+$(content).attr('id')+'';
        result_json.push(build_section_json(content_tag));
    }

    return result_json;
}

function build_section_json(tg){

    tag = $(tg);
    var result = {};
    result.titre = $(tag).find('[name="titre_section"]').val();
    result.sousSections = build_sous_section(tg)
    return result;
}

//sous section
function build_sous_section(tg){
    var result_json = [];

    tag = $(tg);
    var tags = $(tag).find('.sub_section_item');
    for(var i = 0; i<tags.length ; i++){
        const content  = tags[i];
        const content_tag = '#'+$(content).attr('id')+'';
        result_json.push(build_sous_section_json(content_tag));
    }

    return result_json;

}

function build_sous_section_json(tg){
    tag = $(tg);
    var result = {};
    result.titre = $(tag).find('[name="titre_sous_section"]').val();
    result.paragraphes = build_paragraphes(tg);
    return result;
}

//paragraphe
function build_paragraphes(tg){
    var result_json = [];

    tag = $(tg);
    var tags = $(tag).find('.paragraph_item');
    for(var i = 0; i<tags.length ; i++){
        const content  = tags[i];
        const content_tag = '#'+$(content).attr('id')+'';
        result_json.push(build_paragraphe_json(content_tag));
    }
    return result_json;
}

function build_paragraphe_json(tg){
    tag = $(tg);
    var result = {};
    result.titre = $(tag).find('[name="titre_pg"]').val();
    result.questions = build_question(tg)
    return result;
}

//question
function build_question(tg){
    var result_json = [];

    tag = $(tg);
    var tags = $(tag).find('.question_item');
    for(var i = 0; i<tags.length ; i++){
        const content  = tags[i];
        const content_tag = '#'+$(content).attr('id')+'';
        result_json.push(build_question_json(content_tag));
    }
    return result_json;
}

function build_question_json(tg){
    tag = $(tg);
    var question_id = $(tag).attr('id');
    question_id = question_id.replace('question_item_' , '');
    console.log(question_id);
    var result = {};

    mandatory = $(tag).find('[name="mandatory_'+question_id+'"]').prop('checked');
    api = $(tag).find('[name="api_'+question_id+'"]').prop('checked');

    type_1_selected = $(tag).find('#qst_type_champ_'+question_id+'').prop('checked');
    type_2_selected = $(tag).find('#qst_type_param_'+question_id+'').prop('checked');

    result.reponse= "";
    result.required= mandatory;
    result.has_auto_rsp=api;

    if (type_1_selected){
        result.label = $(tag).find('[name="nom_question"]').val();
        const value = $(tag).find('[name="type_question"]').val();
        result.type = value;
        if (value == "select" || value =="checkbox" || value =="radio"){
            const textarea_value = $(tag).find('[name="liste_option"]').val();
            result.option = textarea_value.trim();
        }
    }
    if (type_2_selected){
        const select_tag = $(tag).find('[name="type_question2"]');
        const option_tag = $(tag).find('[name="type_question2"] option:selected');
        const value = $(select_tag).val();
        result.label = $(option_tag).attr('data-label');
        result.type = $(option_tag).attr('data-type');
        result.option = $(option_tag).attr('data-option');
    }

    return result;
}


////FORMULAIRE DE PREVALIDATION
//section
$(document).on('click','.add_section2',{passive:true},function () {
    const section_id = randomId();
    const accordeon_id = randomId();

    const html = '<div id="section_'+section_id+'" class="card section_item2" data-children=".item">'+
        '    <div class="card-header card-header-primary ">'+
        '        <div class="form-row">'+
        '            <a type="button" data-toggle="collapse" data-parent="#section_'+section_id+'" href="#accordion_'+accordeon_id+'" class="collapsed icon-arrow-down col-sm-1 opener text-center mt-2"></a>'+
        '            <div class="col-sm-8">'+
        '                <label>Titre de la Section :</label><input type="text" name="titre_section" placeholder="titre de la section" class="form-control" required>'+
        '            </div>'+
        '            <div class="col-sm-3 text-right">'+
        '                <div class="btn-group">'+
        '                    <button class="btn add_paragraph2" data-id="'+section_id+'" data-toggle="tooltip" data-placement="top" title="Ajouter une sous section" type="button"><i class="icon-add-more"></i></button>' +
        '                    <button class="btn delete_item btn-link text-danger" data-target="#section_'+section_id+'" data-message="cette section et son contenue" data-toggle="tooltip" data-placement="top" title="Supprimer cette section" type="button"><i class="icon-delete"></i></button>' +
        '                </div>'+
        '            </div>'+
        '        </div>'+
        '    </div>'+
        '    <div class="item">'+
        '        <div id="accordion_'+accordeon_id+'" class="collapse" role="tabpanel">'+
        '            <div class="card-body section_content2">'+
        '            </div>'+
        '        </div>'+
        '    </div>'+
        '</div>';

    $(sections_panel2).append(html);

    show_message('success' , 'Section ajouter avec succès')
    setTimeout(function(){
        enable_tooltip();
        $('.add_paragraph2[data-id="'+section_id+'"]').click();
    },300);
});

//paragraphe
$(document).on('click','.add_paragraph2',{passive:true},function () {
    const tag = $(this);
    const paragraph_id = randomId();
    const accordion_paragraph_id = randomId();
    const sub_section_id = $(this).attr('data-id');
    const sub_section_content = '#section_'+sub_section_id+' .section_content2';
    const html = '<div id="paragraph_'+paragraph_id+'" class="card paragraph_item2" data-children=".item">'+
        '    <div class="card-header card-header-primary ">'+
        '        <div class="form-row">'+
        '            <a data-toggle="collapse" data-parent="#paragraph_'+paragraph_id+'" href="#sub_accordion_para_'+accordion_paragraph_id+'" class="collapsed icon-arrow-down col-sm-1 opener text-center mt-2"></a>'+
        '            <div class="col-sm-8">'+
        '                <label>Titre du Paragraphe :</label> <input type="text" name="titre_pg2" placeholder="titre du Paragraphe" class="form-control">'+
        '            </div>'+
        '            <div class="col-sm-3 text-right">'+
        '                <div class="btn-group">'+
        '                <div class="btn-group">'+
        '                    <button class="btn add_question2" data-id="'+paragraph_id+'" data-toggle="tooltip" data-placement="top" title="Ajouter une Question" type="button"><i class="icon-add-more"></i></button>' +
        '                    <button class="btn delete_item btn-link text-danger" data-target="#paragraph_'+paragraph_id+'" data-message="ce paragraphe et son contenue" data-toggle="tooltip" data-placement="top" title="Supprimer ce paragraphe" type="button"><i class="icon-delete"></i></button>' +
        '                </div>'+
        '                </div>'+
        '            </div>'+
        '        </div>'+
        '    </div>'+
        '    <div class="item">'+
        '        <div id="sub_accordion_para_'+accordion_paragraph_id+'" class="collapse" role="tabpanel">'+
        '            <div class="card-body paragraph_content2 form-row">'+
        '            </div>'+
        '        </div>'+
        '    </div>'+
        '</div>';

    $(sub_section_content).append(html);
    show_message('info' , 'Paragraphe ajouter avec succès')
    setTimeout(function(){
        $('.add_question2[data-id="'+paragraph_id+'"]').click();
        enable_tooltip();
    },300);

});

//question
$(document).on('change','.typequestion2',{passive:true},function () {

    const value = $(this).val();
    console.log(value);
    const parent = $(this).parents('.type_champ_select_indicator');
    console.log(parent);
    const question_id = $(parent).attr('data-question_id');
    console.log(question_id);
    const target = $('#question_item_'+question_id+' '+$(parent).attr('data-textarea-cible'));

    if (value == "select" || value =="checkbox" || value =="radio"){
        $(target).show('slow');
    }
    else {
        $(target).hide('slow');

    }


});

$(document).on('click','.add_question2',{passive:true},function () {

    console.log('tests1');
    const tag = $(this);
    const question_id = randomId();
    const paragraph_id = $(this).attr('data-id');
    const paragraph_content = '#paragraph_'+paragraph_id+' .paragraph_content2';

    const qst_number = $(paragraph_content).find('.question_item2').length+1;

    const html =  '<div class="col-sm-4 question_item2" id="question_item_'+question_id+'">'+
        '    <div class="card">'+
        '        <div class="card-header card-header-primary">' +
        '           <div class="form-row">' +
        '            <div class="col-sm-12">'+
        '                <span>Question  '+qst_number+'</span>'+
        '                <button type="button" class="btn btn-link float-right delete_item" data-target="#question_item_'+question_id+'"><i class="icon-delete text-danger"></i></button>'+
        '            </div>'+
        '           </div>       ' +
        '         </div>' +
        '        <div class="card-body">'+
        '            <div class="form-group">'+
        '                <div class="form-row border-bottom">'+
        '                    <div class="col-sm-6">'+
        '                            <label>Mandatory</label>'+
        '                    </div>'+
        '                    <div class="col-sm-2">'+
        '                        <div class="custom-control custom-radio">'+
        '                            <input class="custom-control-input" type="radio" name="mandatory_'+question_id+'" value="oui" data-question_id="'+question_id+'" id="mandatory_oui_'+question_id+'" checked>'+
        '                            <label class="custom-control-label" for="mandatory_oui_'+question_id+'"><small>Oui</small></label>'+
        '                        </div>'+
        '                    </div>'+
        '                    <div class="col-sm-2">'+
        '                        <div class="custom-control custom-radio">'+
        '                            <input class="custom-control-input" type="radio" name="mandatory_'+question_id+'" value="non" data-question_id="'+question_id+'" id="mandatory_non_'+question_id+'" >'+
        '                            <label class="custom-control-label" for="mandatory_non_'+question_id+'"><small>Non</small></label>'+
        '                        </div>'+
        '                    </div>'+
        '                </div>'+
        '                <div class="form-row select_type_'+question_id+'">'+
        '                    <div class="col-sm-12">'+
        '                        <div class="type_item type_champ_select_indicator" data-question_id="'+question_id+'" id="type_champ_select_'+question_id+'" data-textarea-cible="#question_textarea_'+question_id+'">'+
        '                           <div class="form-group">'+
        '                               <label>Nom de la question</label>'+
        '                               <input type="text" class="form-control" name="nom_question" required>'+
        '                           </div>'+
        '                           <div class="form-group">'+
        '                               <label class="">Type du champ</label>' +
                                            type_champ_select_dropdown+
        '                               <div class="mt-3" id="question_textarea_'+question_id+'" style="display: none">'+
        '                                   <textarea name="liste_option" rows="5" class="form-control" placeholder="Renseigner les differentes options ici, séparées de point virgules(;)"></textarea>'+
        '                               </div>'+
        '                           </div>'+
        '                        </div>'+
        '                    </div>'+
        '                </div>'+
        '            </div>'+
        '        </div>'+
        '    </div>'+
        '</div>';
    show_message('warning' , 'Question ajouter avec succès');
    $(paragraph_content).append(html);
    
    console.log('tests2');
    setTimeout(function(){
        enable_tooltip();
    },300);

});

//section
function build_section2(){
    var result_json = [];

    var tags = $('.section_item2');
    for(var i = 0; i<tags.length ; i++){
        const content  = tags[i];
        const content_tag = '#'+$(content).attr('id')+'';
        result_json.push(build_section_json2(content_tag));
    }

    return result_json;
}

function build_section_json2(tg){

    tag = $(tg);
    var result = {};
    result.titre = $(tag).find('[name="titre_section"]').val();
    result.paragraphes = build_paragraphes2(tg)
    return result;
}

//paragraphe
function build_paragraphes2(tg){
    var result_json = [];

    tag = $(tg);
    var tags = $(tag).find('.paragraph_item2');
    for(var i = 0; i<tags.length ; i++){
        const content  = tags[i];
        const content_tag = '#'+$(content).attr('id')+'';
        result_json.push(build_paragraphe_json2(content_tag));
    }
    return result_json;
}

function build_paragraphe_json2(tg){
    tag = $(tg);
    var result = {};
    result.titre = $(tag).find('[name="titre_pg2"]').val();
    result.questions = build_question2(tg)
    return result;
}

//question
function build_question2(tg){
    var result_json = [];

    tag = $(tg);
    var tags = $(tag).find('.question_item2');
    for(var i = 0; i<tags.length ; i++){
        const content  = tags[i];
        const content_tag = '#'+$(content).attr('id')+'';
        result_json.push(build_question_json2(content_tag));
    }
    return result_json;
}

function build_question_json2(tg){
    tag = $(tg);
    var question_id = $(tag).attr('id');
    question_id = question_id.replace('question_item_' , '');
    console.log(question_id);
    var result = {};

    mandatory = $(tag).find('[name="mandatory_'+question_id+'"]').prop('checked');

    result.reponse= "";
    result.required= mandatory;

    result.label = $(tag).find('[name="nom_question"]').val();
    const value = $(tag).find('[name="type_question"]').val();
    result.type = value;
    if (value == "select" || value =="checkbox" || value =="radio"){
        const textarea_value = $(tag).find('[name="liste_option"]').val();
        result.option = textarea_value.trim();
    }
   

    return result;
}


///PARAMETRE DE DYSFONCTIONNEMENT
//question

$(document).on('change','.typequestion3',{passive:true},function () {

    const value = $(this).val();
    console.log(value);
    const parent = $(this).parents('.type_champ_select_indicator');
    console.log(parent);
    const question_id = $(parent).attr('data-question_id');
    console.log(question_id);
    const target = $('#question_item_'+question_id+' '+$(parent).attr('data-textarea-cible'));

    if (value == "select" || value =="checkbox" || value =="radio"){
        $(target).show('slow');
    }
    else {
        $(target).hide('slow');

    }


});

$(document).on('click','.add_question3',{passive:true},function () {

    const question_id = randomId();
    const paragraph_id = $(this).attr('data-id');
    const paragraph_content = '#paragraph_'+paragraph_id+' .paragraph_content3';

    const qst_number = $(paragraph_content).find('.question_item3').length+1;

    const html =  '<div class="col-sm-4 question_item3" id="question_item_'+question_id+'">'+
        '    <div class="card">'+
        '        <div class="card-header card-header-primary">' +
        '           <div class="form-row">' +
        '            <div class="col-sm-12">'+
        '                <span>Question  '+qst_number+'</span>'+
        '                <button type="button" class="btn btn-link float-right delete_item" data-target="#question_item_'+question_id+'"><i class="icon-delete text-danger"></i></button>'+
        '            </div>'+
        '           </div>       ' +
        '         </div>' +
        '        <div class="card-body">'+
        '            <div class="form-group">'+
        '                <div class="form-row border-bottom">'+
        '                    <div class="col-sm-6">'+
        '                            <label>Mandatory</label>'+
        '                    </div>'+
        '                    <div class="col-sm-2">'+
        '                        <div class="custom-control custom-radio">'+
        '                            <input class="custom-control-input" type="radio" name="mandatory_'+question_id+'" value="oui" data-question_id="'+question_id+'" id="mandatory_oui_'+question_id+'" checked>'+
        '                            <label class="custom-control-label" for="mandatory_oui_'+question_id+'"><small>Oui</small></label>'+
        '                        </div>'+
        '                    </div>'+
        '                    <div class="col-sm-2">'+
        '                        <div class="custom-control custom-radio">'+
        '                            <input class="custom-control-input" type="radio" name="mandatory_'+question_id+'" value="non" data-question_id="'+question_id+'" id="mandatory_non_'+question_id+'" >'+
        '                            <label class="custom-control-label" for="mandatory_non_'+question_id+'"><small>Non</small></label>'+
        '                        </div>'+
        '                    </div>'+
        '                </div>'+
        '                <div class="form-row select_type_'+question_id+'">'+
        '                    <div class="col-sm-12">'+
        '                        <div class="type_item type_champ_select_indicator" data-question_id="'+question_id+'" id="type_champ_select_'+question_id+'" data-textarea-cible="#question_textarea_'+question_id+'">'+
        '                           <div class="form-group">'+
        '                               <label>Nom de la question</label>'+
        '                               <input type="text" class="form-control" name="nom_question" required>'+
        '                           </div>'+
        '                           <div class="form-group">'+
        '                               <label>Réponse à la question</label>'+
        '                               <input type="text" class="form-control" name="reponse_question">'+
        '                           </div>'+
        '                           <div class="form-group">'+
        '                               <label class="">Type du champ</label>' +
                                            type_champ_select_dropdown+
        '                               <div class="mt-3" id="question_textarea_'+question_id+'" style="display: none">'+
        '                                   <textarea name="liste_option" rows="5" class="form-control" placeholder="Renseigner les differentes options ici, séparées de point virgules(;)"></textarea>'+
        '                               </div>'+
        '                           </div>'+
        '                        </div>'+
        '                    </div>'+
        '                </div>'+
        '            </div>'+
        '        </div>'+
        '    </div>'+
        '</div>';
    show_message('warning' , 'Question ajouter avec succès');
    $(paragraph_content).append(html);
    setTimeout(function(){
        enable_tooltip();
    },300);

});

//question
function build_question3(){
    var result_json = [];

    var tags = $('.question_item3');
    for(var i = 0; i<tags.length ; i++){
        const content  = tags[i];
        const content_tag = '#'+$(content).attr('id')+'';
        result_json.push(build_question_json3(content_tag));
    }
    return result_json;

}

function build_question_json3(tg){
    tag = $(tg);
    var question_id = $(tag).attr('id');
    question_id = question_id.replace('question_item_' , '');
    console.log(question_id);
    var result = {};

    mandatory = $(tag).find('[name="mandatory_'+question_id+'"]').prop('checked');

    result.reponse= $(tag).find('[name="reponse_question"]').val();;
    result.required= mandatory;
    
    result.label = $(tag).find('[name="nom_question"]').val();
    const value = $(tag).find('[name="type_question"]').val();
    result.type = value;
    if (value == "select" || value =="checkbox" || value =="radio"){
        const textarea_value = $(tag).find('[name="liste_option"]').val();
        result.option = textarea_value.trim();
    }

    return result;
}

