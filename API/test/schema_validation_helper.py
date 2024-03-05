from jsonschema import validate, ValidationError, SchemaError


def validate_schema(instance, schema):
    try:
        validate(instance, schema)
    except SchemaError as e:
        print("There is an error with the schema:", e)
        assert False
    except ValidationError as e:
        print(e)

        print("---------")
        print(e.absolute_path)

        print("---------")
        print(e.absolute_schema_path)
        assert False
