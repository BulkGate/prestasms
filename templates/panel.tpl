<div class="panel">
    <div class="panel-heading">
        <i class="icon-envelope-alt"></i> PrestaSMS
    </div>
    <div id="presta-sms" style="margin: 0">
        <div id="react-snack-root"></div>
        <div id="react-app-root">
            <p>{'Loading content'|prestaSmsTranslate}</p>
        </div>
        <script type="application/javascript">
            var _bg_client_config = {
                url: {
                    authenticationService : 'index.php',
                },
                actions: {
                    authenticate: function () {
                        return {
                            data: {$authenticate|prestaSmsEscapeJs}
                        }
                    }
                }
            };
        </script>
        <script src="{$widget_api_url|prestaSmsEscapeUrl}"></script>
        <script type="application/javascript">
            _bg_client.registerMiddleware(function (data)
            {
                if(data.init._generic)
                {
                    data.init.env.homepage.logo_link = {$logo|prestaSmsEscapeJs};
                    data.init._generic.scope.module_info = {$info|prestaSmsEscapeJs}
                }
            });

            var input = _bg_client.parseQuery(location.search);

            _bg_client.require({$application_id|prestaSmsEscapeJs}, {
                product: "ps",
                language: {$language|prestaSmsEscapeJs},
                salt: {$salt|prestaSmsEscapeJs},
                view: {
                    presenter: {$presenter|prestaSmsEscapeJs},
                    action: {$action|prestaSmsEscapeJs},
                },
                params: {
                    id: {if isset($id) && $id}{$id|prestaSmsEscapeJs}{else}input["id"]{/if},
                    key: {if isset($key) && $key}{$key|prestaSmsEscapeJs}{else}input["key"]{/if},
                    type: {if isset($type) && $type}{$type|prestaSmsEscapeJs}{else}input["type"]{/if},
                },
                proxy: {$proxy|prestaSmsEscapeJs},
            });
        </script>
    </div>
</div>
