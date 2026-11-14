"""Real multipart upload regression inside the isolated HTTP fixture."""
import base64
import urllib.request


def check_upload(resident, admin, Client, query, base, prefix):
    image = base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/l9sAAAAASUVORK5CYII=')
    fields = dict(action='create_incident', incident_type='other', description='Fictional upload test', reporter_name='Fictional Reporter', contact_phone='0000000000', location_description='Fictional landmark', latitude='', longitude='')
    def upload(content, filename):
        boundary = 'BayanReadyUploadTestBoundary'
        body = b''
        for key, value in fields.items():
            body += ('--' + boundary + '\r\nContent-Disposition: form-data; name="' + key + '"\r\n\r\n' + value + '\r\n').encode()
        body += ('--' + boundary + '\r\nContent-Disposition: form-data; name="image"; filename="' + filename + '"\r\nContent-Type: image/png\r\n\r\n').encode() + content
        body += ('\r\n--' + boundary + '--\r\n').encode()
        return resident.client.open(urllib.request.Request(base + prefix + 'incidents/create.php', data=body, headers={'Content-Type':'multipart/form-data; boundary=' + boundary}))
    response = upload(image, 'fictional.png')
    html = response.read().decode()
    assert 'alert-danger' not in html and 'incidents/view.php?id=' in response.geturl()
    row = query('SELECT * FROM incident_images ORDER BY id DESC LIMIT 1')[0]
    path = prefix + 'incidents/image.php?id=' + str(row['id'])
    for client in [resident, admin]:
        response = client.client.open(base + path)
        assert response.headers.get_content_type() == 'image/png' and response.read() == image
    anonymous = Client()
    anonymous.request(path)
    assert anonymous.last_url.endswith('/index.php')
    count = query('SELECT COUNT(*) AS n FROM incident_reports')[0]['n']
    assert 'Choose a valid' in upload(b'not an image', 'fake.png').read().decode()
    assert query('SELECT COUNT(*) AS n FROM incident_reports')[0]['n'] == count
    print('PASS multipart PNG upload, stored-image retrieval for owner/admin and invalid-image rejection')
