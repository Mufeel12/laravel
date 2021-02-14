<!-- global vars -->
<?php /* ATTENTION! Removing these or not including these on any page will break the site! */ ?>
<script type="text/javascript">
    window.HELP_IMPROVE_VIDEOJS = false;
    window.vttLocation = "{!! asset('js/vtt.js') !!}";
    var debugMode = {!! env('APP_DEBUG') or 'false' !!};
    var root = '{!! url('/') !!}';
    var projectRemoveCategory = "{!! asset('img/project_category_close.png') !!}";
    var projectMinimizeCategory = "{!! asset('img/project_category_min.png') !!}";
    var route = {
    };
    @if (\Illuminate\Support\Facades\Auth::user())
    <?php $freeSpace = \App\User::getFreeSpace(); ?>
    window.user = {
        id: <?php echo \Illuminate\Support\Facades\Auth::id() ?>,
        freeSpace: <?php echo $freeSpace ?>,
        freeSpaceFormatted: '<?php echo format_bytes($freeSpace); ?>',
        avatar: '<?php echo (isset($settings) ? $settings->avatar : '') ?>'
    };
    @endif

    @if(isset($project->id))
    var projectId = "{!! $project->id !!}";
    window.projectId = {!! $project->id !!};
    @endif
    @if(isset($collaborators))
    var collaborators = {!! $collaborators !!};
    /*jQuery(document).ready(function($) {
        $('.dropdown-menu input, .dropdown-menu label').click(function(e) {
            e.stopPropagation();
        });
    });*/
    @endif

</script>