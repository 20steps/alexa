'use strict';

angular.module('twentysteps.alexa')

    .config(['MODULE_APP', 'NgAdminConfigurationProvider',
        function (MODULE_APP, NgAdminConfigurationProvider) {

            // create an admin application
            var nga = NgAdminConfigurationProvider;
            var admin =
                nga
                    .application('Datenpflege')
                    .baseApiUrl('')
                                        .debug(false);
                        ;

                        var User =
            nga.entity('users');
                                    User
                .label('Nutzer')
            
                        User.listView()
                .fields([
                                                                nga.field('id', 'number')
                                            .label('Id')
                                            .editable(false)                                                                                                                                            ,
                                                                nga.field('email', 'string')
                                            .label('E-Mail')
                                                                                                                                                                                        ,
                                                                nga.field('firstName', 'string')
                                            .label('Vorname')
                                                                                                                                                                                        ,
                                    ])
                .listActions([
                                        'show',
                                        'edit',
                                        'delete',
                                    ])
                                .title('Registrierte Nutzer')
                                                .sortField('id')
                .sortDir('DESC')
                                                .filters([
                                        nga.field('email', 'string').label('E-Mail')
                                            .pinned(true)
                                        ,
                                    ])
                        ;
            
            
            User.
            showView()
                .fields([
                                                                nga.field('id', 'number')
                            .label('Id')
                                            .editable(false)                                                                                                                                            ,
                                                                nga.field('slug', 'string')
                            .label('Slug')
                                            .editable(false)                                                                                                                                            ,
                                                                nga.field('username', 'string')
                            .label('Nutzername')
                                                                                                                                                                                        ,
                                                                nga.field('enabled', 'boolean')
                            .label('aktiviert')
                                                                                                                                                                                        ,
                                                                nga.field('email', 'string')
                            .label('E-Mail')
                                                                                                                                                                                        ,
                                                                nga.field('firstName', 'string')
                            .label('Vorname')
                                                                                                                                                                                        ,
                                                                nga.field('lastName', 'string')
                            .label('Nachname')
                                                                                                                                                                                        ,
                                                                nga.field('createdAt', 'datetime')
                            .label('Registriert am')
                                            .editable(false)                                                                                                                                            ,
                                                                nga.field('updatedAt', 'datetime')
                            .label('Letzter Login')
                                            .editable(false)                                                                                                                                            ,
                                    ])
                                .title('Registrierter Nutzer #{{ entry.values.id }}')
                                            ;
            
            
            User.
            creationView()
                .fields([
                                                                nga.field('username', 'string')
                            .label('Nutzername')
                                                                                        .validation({
                                                    })
                                                                                                                            ,
                                                                nga.field('enabled', 'boolean')
                            .label('aktiviert')
                                                                                        .validation({
                            required: true,                        })
                                                                                                                            ,
                                                                nga.field('email', 'string')
                            .label('E-Mail')
                                                                                        .validation({
                                                    })
                                                                                                                            ,
                                                                nga.field('firstName', 'string')
                            .label('Vorname')
                                                                                        .validation({
                                                    })
                                                                                                                            ,
                                                                nga.field('lastName', 'string')
                            .label('Nachname')
                                                                                        .validation({
                                                    })
                                                                                                                            ,
                                                                nga.field('createdAt', 'datetime')
                            .label('Registriert am')
                                            .editable(false)                                            .validation({
                                                    })
                                                                                                                            ,
                                                                nga.field('updatedAt', 'datetime')
                            .label('Letzter Login')
                                            .editable(false)                                            .validation({
                                                    })
                                                                                                                            ,
                                    ])
                                            ;
            
                        User.
            editionView()
                .fields([
                                                                nga.field('id', 'number')
                            .label('Id')
                                            .editable(false)                                                                .validation({
                                                    })
                                                                                                                            ,
                                                                nga.field('slug', 'string')
                            .label('Slug')
                                            .editable(false)                                                                .validation({
                                                    })
                                                                                                                            ,
                                                                nga.field('username', 'string')
                            .label('Nutzername')
                                                                                                            .validation({
                                                    })
                                                                                                                            ,
                                                                nga.field('enabled', 'boolean')
                            .label('aktiviert')
                                                                                                            .validation({
                            required: true,                        })
                                                                                                                            ,
                                                                nga.field('email', 'string')
                            .label('E-Mail')
                                                                                                            .validation({
                                                    })
                                                                                                                            ,
                                                                nga.field('firstName', 'string')
                            .label('Vorname')
                                                                                                            .validation({
                                                    })
                                                                                                                            ,
                                                                nga.field('lastName', 'string')
                            .label('Nachname')
                                                                                                            .validation({
                                                    })
                                                                                                                            ,
                                                                nga.field('createdAt', 'datetime')
                            .label('Registriert am')
                                            .editable(false)                                                                .validation({
                                                    })
                                                                                                                            ,
                                                                nga.field('updatedAt', 'datetime')
                            .label('Letzter Login')
                                            .editable(false)                                                                .validation({
                                                    })
                                                                                                                            ,
                                    ])
                                            ;
            
                        User.
            deletionView()
                                        ;
            
            admin.addEntity(User);

            
            // configure menu
            admin.menu(nga.menu()
                                    .addChild(nga.menu(User)
                        .title('Nutzer')
                        .icon('<span class="glyphicon glyphicon-user"></span>'))
                                );

            // configure dashboard
                            admin.dashboard(nga.dashboard()
                                            .template('\x3Cdiv\x20ng\x2Dinclude\x3D\x22\x27\x2Fbundles\x2Fbrickscustomtwentystepsalexa\x2FSPA\x2Fmodules\x2Fdashboard\x2Fadmin\x2Fhome\x2Ftemplate.html\x27\x22\x3E\x3C\x2Fdiv\x3E')
                                    );
            
            // attach the admin application to the DOM and run it
            nga.configure(admin);

        }])

    .run(['bricksKernel',
        function (bricksKernel) {
        }
    ])


;