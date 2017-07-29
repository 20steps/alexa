'use strict';

angular.module('twentysteps.alexa')

    
        .factory('User',function(bricksAPIRESTProjectRestangularService,bricksAPIRESTAuthenticatedProjectRestangularService,$log) {
            var service = bricksAPIRESTProjectRestangularService.service('users');
            service.serviceAuth = bricksAPIRESTAuthenticatedProjectRestangularService;
            service.service = bricksAPIRESTProjectRestangularService;
            service.$log = $log;
            service.getBy = function(parameter) {
                parameter.single=true;
                $log.debug('User.getBy',parameter);
                return this.service.oneUrl('users','').get(parameter);
            };
            service.removeById = function(id) {
                $log.debug('User.removeById',id);
                return this.serviceAuth.one('users/'+id).customDELETE();
            };
            service.updateById = function(id,data) {
                $log.debug('User.updateById',id,data);
                return this.serviceAuth.one('users/'+id).customPUT(data);
            };
            service.create = function(data) {
                $log.debug('User.create',data);
                return this.serviceAuth.one('users').customPOST(data);
            };
                        return service;
        })

    
    .run(['bricksAPIRESTProjectRestangularService', 'bricksAPIRESTAuthenticatedProjectRestangularService', '$log',
        function(bricksAPIRESTProjectRestangularService, bricksAPIRESTAuthenticatedProjectRestangularService,$log) {
            bricksAPIRESTProjectRestangularService.setOnElemRestangularized(function(elem,isCollection,what,Restangular) {
                elem.serviceAuth = bricksAPIRESTAuthenticatedProjectRestangularService;
                elem.service = bricksAPIRESTProjectRestangularService;
                elem.$log = $log;
                                    if (isCollection && what == 'users') {
                        elem.getBy = function(parameter) {
                            parameter.single=true;
                            $log.debug('User.getBy',parameter);
                            return this.service.oneUrl('users','').get(parameter);
                        };
                        elem.removeById = function(id) {
                            $log.debug('User.removeById',id);
                            return this.serviceAuth.one('users/'+id).customDELETE();
                        };
                        elem.updateById = function(id,data) {
                            $log.debug('User.updateById',id,data);
                            return this.serviceAuth.one('users/'+id).customPUT(data);
                        };
                        elem.create = function(data) {
                            $log.debug('User.create',data);
                            return this.serviceAuth.one('users').customPOST(data);
                        };
                    } else if (!isCollection && what == 'users') {
                        elem.remove = function() {
                            $log.debug('User.remove',elem);
                            return this.serviceAuth.one('users/'+elem.id).customDELETE();
                        };
                        elem.update = function(data) {
                            $log.debug('User.update',elem,data);
                            return this.serviceAuth.one('users/'+elem.id).customPUT(data);
                        };
                    }
                                                                        return elem;
            });
        }
    ])

;