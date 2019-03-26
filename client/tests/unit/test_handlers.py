import unittest
import sys
import os
import inspect
from unittest_data_provider import data_provider

sys.path.append(os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe()))) + '/../')

from bahub.bahubapp.entity.access import ServerAccess
from bahub.bahubapp.entity.encryption import Encryption
from bahub.bahubapp.handler import FileRepositoryClient, Logger, PipeFactory
from bahub.bahubapp.handler.localfilebackup import LocalFileBackup, LocalFileDefinition
from bahub.bahubapp.handler.commandoutputbackup import CommandOutputBackup, CommandOutputDefinition


class HandlersTest(unittest.TestCase):
    """
    Generic test for all handlers with a data provider
    """

    def failure_cases():
        server_access = ServerAccess.from_config({
            'url': 'http://some-server',
            'token': '123'
        })

        encryption = Encryption.from_config({
            'passphrase': 'test-123',
            'method': 'aes-128-cbc'
        })

        return [
            # directory: the directory does not exist
            [
                LocalFileDefinition.from_config({
                    'type': 'directory',
                    'access': server_access,
                    'collection_id': 'does-not-matter-in-this-test',
                    'encryption': encryption,
                    'paths': ['/etc/something-that-does-not-exist']
                }),
                LocalFileBackup,
                'Path "/etc/something-that-does-not-exist" does not exist'
            ],

            # command: the command returns non-zero exit code
            [
                CommandOutputDefinition.from_config({
                    'access': server_access,
                    'type': 'command',
                    'collection_id': 'does-not-matter-in-this-test',
                    'encryption': encryption,
                    'command': 'cat /some-non-existing-file'
                }),
                CommandOutputBackup,
                'The process exited with incorrect code, try to verify the command in with --debug switch'
            ]
        ]

    @data_provider(failure_cases)
    def test_handlers_fails_everything_when_any_part_fails(self, definition, handler_type, expected_message):
        handler = handler_type(
            _client=self._get_client_mock(),
            _logger=Logger('test'),
            _definition=definition,
            _pipe_factory=PipeFactory()
        )

        msg = ""

        try:
            handler.perform_backup()
        except Exception as e:
            msg = str(e)

        self.assertEqual(msg, expected_message)

    def success_cases():
        server_access = ServerAccess.from_config({
            'url': 'http://some-server',
            'token': '123'
        })

        encryption = Encryption.from_config({
            'passphrase': 'test-123',
            'method': 'aes-128-cbc'
        })

        return [
            [
                LocalFileDefinition.from_config({
                    'type': 'directory',
                    'access': server_access,
                    'collection_id': 'does-not-matter-in-this-test',
                    'encryption': encryption,
                    'paths': ['/etc']
                }),
                LocalFileBackup
            ],

            [
                CommandOutputDefinition.from_config({
                    'access': server_access,
                    'type': 'command',
                    'collection_id': 'does-not-matter-in-this-test',
                    'encryption': encryption,
                    'command': 'cat /etc/hosts'
                }),
                CommandOutputBackup
            ]
        ]

    @data_provider(success_cases)
    def test_handlers_successfully_gzips_data(self, definition, handler_type):
        handler = handler_type(
            _client=self._get_client_mock(),
            _logger=Logger('test'),
            _definition=definition,
            _pipe_factory=PipeFactory()
        )

        handler.perform_backup()

    def _get_client_mock(self):
        def _send(read_stream, definition):
            read_stream.read()
            return read_stream

        client = FileRepositoryClient(Logger('test'))
        client.send = _send

        return client
