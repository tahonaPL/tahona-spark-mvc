<?php
/**
 * Date: 12.01.19
 * Time: 01:16
 */

namespace Spark\Core\Processor\Loader;


class ContextType {
    public const ROUTE = 'route';
    public const CONFIG = 'config';
    public const INTERCEPTORS = 'interceptors';
    public const HTTP_FILTERS = 'httpFilters';
    public const CONTROLLER = 'controller';
    public const EXCEPTION_RESOLVERS = 'exceptionResolvers';
    public const GLOBAL_ERROR_HANDLER = 'globalErrorHandler';
    public const COMMANDS = 'commands';
    public const LANG_RESOURCES = 'langResources';
    public const LANG_RESOURCE_PATHS = 'langResourcePaths';
    public const SESSION_PROVIDER = 'sessionProvider';
    public const VIEW_HANDLERS = 'viewHandlers';
    public const FILLERS = 'fillers';
    public const REQUEST_PROVIDER = 'requestProvider';
}