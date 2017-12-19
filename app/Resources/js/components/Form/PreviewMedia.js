// @flow
import * as React from 'react';
import { Col, Label } from 'react-bootstrap';
import { FormattedMessage } from 'react-intl';

type Media = {
  id: string,
  name: string,
  extension: string,
  url: string,
};

type Props = {
  medias: Array<Media>,
  onRemoveMedia: (newMedia: Media) => void,
};

type State = {
  initialMedias: Array<Media>,
};

export class PreviewMedia extends React.PureComponent<Props, State> {
  constructor(props: Props) {
    super(props);
    this.state = {
      initialMedias: props.medias,
    };
  }

  render() {
    const { medias, onRemoveMedia } = this.props;

    if (medias.length === 0) {
      return null;
    }

    return (
      <div>
        {medias &&
          medias.length > 0 && (
            <Col md={12} className="image-uploader__label-info" style={{ padding: 0 }}>
              <strong>
                <FormattedMessage id="proposal.documents.deposited" />
              </strong>{' '}
              {medias.map((file, key) => {
                return (
                  <Label key={key} bsStyle="info" style={{ marginRight: '5px' }}>
                    {file.name}{' '}
                    <i
                      style={{ cursor: 'pointer' }}
                      className="glyphicon glyphicon-remove"
                      onClick={() => {
                        onRemoveMedia(file);
                      }}
                    />
                  </Label>
                );
              })}
            </Col>
          )}
      </div>
    );
  }
}

export default PreviewMedia;
