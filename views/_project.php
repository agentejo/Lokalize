<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/lokalize')">@lang('Lokalize')</a></li>
        <li class="uk-active"><span>@lang('Project')</span></li>
    </ul>
</div>

<div riot-view>

    <form class="uk-form uk-width-medium-1-2 uk-container-center" onsubmit="{ submit }">

        <div class="uk-panel uk-panel-space uk-panel-box uk-panel-card">

           <span class="uk-badge">@lang('General')</span>

           <div class="uk-margin">
               <label class="uk-text-small">@lang('Name')</label>
               <input class="uk-width-1-1 uk-form-large" type="text" ref="name" bind="project.name" required>
           </div>

           <div class="uk-margin">
               <label class="uk-text-small">@lang('Color')</label>
               <div class="uk-margin-small-top">
                   <field-colortag bind="project.color" title="@lang('Color')" size="20px"></field-colortag>
               </div>
           </div>

           <div class="uk-margin">
               <label class="uk-text-small">@lang('Description')</label>
               <textarea class="uk-width-1-1 uk-form-large" name="description" bind="project.info" bind-event="input" rows="5"></textarea>
           </div>

           <div class="uk-margin">
               <label class="uk-text-small">@lang('Default Language')</label>
               <div class="uk-margin-small-top">
                   <select class="uk-select uk-width-1-1" bind="project.lang">
                       @foreach($app->module('lokalize')->languages() as $key => $value)
                        <option value="{{$key}}">{{$value}}</option>
                       @end
                   </select>
               </div>
           </div>

       </div>


        <div class="uk-margin-large-top" show="{ project.name }">

            <div class="uk-button-group uk-margin-right">
                <button class="uk-button uk-button-large uk-button-primary">@lang('Save')</button>
                <a class="uk-button uk-button-large" href="@route('/lokalize/project')/{ project._id }" if="{ project._id }"><i class="uk-icon-list"></i> @lang('Manage keys')</a>
            </div>

            <a href="@route('/lokalize')">
                <span show="{ !project._id }">@lang('Cancel')</span>
                <span show="{ project._id }">@lang('Close')</span>
            </a>
        </div>


    </form>



    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.project = {{ json_encode($project) }};
        this.aclgroups = {{ json_encode($aclgroups) }};

        this.on('mount', function(){

            this.trigger('update');

            // bind clobal command + save
            Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {

                e.preventDefault();
                $this.submit();
                return false;
            });
        });


        submit(e) {

            if (e) e.preventDefault();

            App.request('/lokalize/save_project', {project: this.project}).then(function(project) {

                App.ui.notify("Saving successful", "success");
                $this.project = project;
                $this.update();

            }).catch(function() {
                App.ui.notify("Saving failed.", "danger");
            });
        }

    </script>
</div>
