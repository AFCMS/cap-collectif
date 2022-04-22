// @flow
import * as React from 'react';
import { graphql, createFragmentContainer } from 'react-relay';
import { connect, type MapStateToProps } from 'react-redux';
import { FormattedMessage } from 'react-intl';
import moment from 'moment';
import { OverlayTrigger, Label, Popover } from 'react-bootstrap';
import type { UnpublishedLabel_publishable } from './__generated__/UnpublishedLabel_publishable.graphql';
import type { GlobalState } from '../../types';

type Props = {
  publishable: UnpublishedLabel_publishable,
  viewer: { email: string },
};

export class UnpublishedLabel extends React.Component<Props> {
  render() {
    const { publishable, viewer } = this.props;
    if (publishable.published) {
      return null;
    }
    let overlay = null;
    if (publishable.notPublishedReason === 'WAITING_AUTHOR_CONFIRMATION') {
      overlay = (
        <Popover
          id={`publishable-${publishable.id}-not-accounted-popover`}
          title={
            <strong>
              <FormattedMessage id="account-pending-confirmation" />
            </strong>
          }>
          <p>
            <FormattedMessage
              id="account-pending-confirmation-message"
              values={{ contentType: 'contribution', emailAddress: viewer.email }}
            />
          </p>
          {publishable.publishableUntil && (
            <p>
              <FormattedMessage
                id="remaining-time"
                values={{
                  remainingTime: moment(publishable.publishableUntil).toNow(true),
                  contentType: 'contribution',
                }}
              />
            </p>
          )}
        </Popover>
      );
    }
    if (publishable.notPublishedReason === 'AUTHOR_NOT_CONFIRMED') {
      overlay = (
        <Popover
          id={`publishable-${publishable.id}-not-accounted-popover`}
          title={
            <strong>
              <FormattedMessage id="account-not-confirmed-in-time" />
            </strong>
          }>
          <FormattedMessage id="account-not-confirmed-in-time-message" />
        </Popover>
      );
    }
    if (publishable.notPublishedReason === 'AUTHOR_CONFIRMED_TOO_LATE') {
      overlay = (
        <Popover
          id={`publishable-${publishable.id}-not-accounted-popover`}
          title={
            <strong>
              <FormattedMessage id="account-confirmed-too-late" />
            </strong>
          }>
          <FormattedMessage id="account-confirmed-too-late-message" />
        </Popover>
      );
    }

    return (
      <span>
        {' '}
        <OverlayTrigger placement="top" overlay={overlay}>
          <Label bsStyle="danger">
            <i className="cap cap-delete-2" />{' '}
            <FormattedMessage
              id={
                publishable.notPublishedReason === 'WAITING_AUTHOR_CONFIRMATION'
                  ? 'awaiting-publication'
                  : 'not-accounted'
              }
            />
          </Label>
        </OverlayTrigger>
      </span>
    );
  }
}

const mapStateToProps: MapStateToProps<*, *, *> = (state: GlobalState) => ({
  viewer: state.user.user,
});

const container = connect(mapStateToProps)(UnpublishedLabel);

export default createFragmentContainer(container, {
  publishable: graphql`
    fragment UnpublishedLabel_publishable on Publishable {
      id
      published
      notPublishedReason
      publishableUntil
    }
  `,
});
