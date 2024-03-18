# *************************************************************************************************
# FileName : conftest.py
# Author   : Christian Marty
# Date     : 16.02.2024
# License  : MIT
# Website  : www.christian-marty.ch
# *************************************************************************************************
import mysql.connector
import eerp
import pytest
import os


def db_run(db, query):
    cursor = db.cursor()
    result = cursor.execute(query, multi=True)
    for res in result:
        print("Running query: ", res)
        print(f"Affected {res.rowcount} rows")
    db.commit()


def load_standard_db(db):
    db_run(db, "DROP DATABASE `eerp_test`;")
    db_run(db, "CREATE DATABASE `eerp_test`;")
    db_run(db, "USE `eerp_test`;")

    folder = "../SQL/install"
    for file in os.listdir(folder):
        if file.endswith(".sql"):
            sql_code = open(os.path.join(folder, file), 'r').read()
            db_run(db, sql_code)

    folder = "../SQL/testdata"
    for file in os.listdir(folder):
        if file.endswith(".sql"):
            sql_code = open(os.path.join(folder, file), 'r').read()
            db_run(db, sql_code)

@pytest.fixture(scope='session')
def db_connection():
    """Create a database connection at the session level."""
    print("Connect to database")
    db_connection = mysql.connector.connect(
        host="192.168.1.200",
        port=3366,
        user="root",
        password="test12345"
    )
    load_standard_db(db_connection)
    yield db_connection  # Yield the connection object
    db_connection.close()  # Clean up after all tests


@pytest.fixture(scope='session')
def eerp_connection():
    """Create the eerp-api connection at the session level."""
    print("Connect to API")
    eerp_connection = eerp.Eerp()
    eerp_connection.login()
    yield eerp_connection  # Yield the connection object
    eerp_connection.logout()  # Clean up after all tests


def pytest_configure(config):
    """
    Allows plugins and conftest files to perform initial configuration.
    This hook is called for every plugin and initial conftest
    file after command line options have been parsed.
    """


def pytest_sessionstart(session):
    """
    Called after the Session object has been created and
    before performing collection and entering the run test loop.
    """


def pytest_sessionfinish(session, exitstatus):
    """
    Called after whole test run finished, right before
    returning the exit status to the system.
    """


def pytest_unconfigure(config):
    """
    called before test process is exited.
    """
