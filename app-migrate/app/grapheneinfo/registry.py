class Registry(object):

    def __init__(self):
        self._registry = {}
        self._registry_models = {}
        self._registry_composites = {}

    def register(self, cls):
        from .types import SQLAlchemyObjectType
        assert issubclass(
            cls, SQLAlchemyObjectType), 'Only SQLAlchemyObjectType can be registered, received "{}"'.format(
            cls.__name__)
        assert cls._meta.registry == self, 'Registry for a Model have to match.'

        self._registry[cls._meta.model] = cls

    def get_type_for_model(self, model):
        return self._registry.get(model)


registry = None


def get_global_registry():
    global registry
    if not registry:
        registry = Registry()
    return registry


def reset_global_registry():
    global registry
    registry = None
