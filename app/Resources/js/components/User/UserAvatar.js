// @flow
import React from 'react';
import { connect, type MapStateToProps } from 'react-redux';
import DefaultAvatar from './DefaultAvatar';
import type { State } from '../../types';

type Props = {
  user: Object,
  size?: number,
  className?: string,
  defaultAvatar: ?string,
  style?: Object,
  anchor?: boolean,
  onBlur?: Function,
  onFocus?: Function,
  onMouseOver?: Function,
  onMouseOut?: Function,
};

export class UserAvatar extends React.Component<Props> {
  static DefaultProps = {
    user: null,
    size: 45,
    className: '',
    style: {},
    anchor: true,
    onBlur: () => {},
    onFocus: () => {},
    onMouseOver: () => {},
    onMouseOut: () => {},
  };

  displayName: 'UserAvatar';

  renderAvatar() {
    const { user, defaultAvatar, size } = this.props;
    const mediaSize = size && `${size}px`;

    if (user && user.media) {
      return (
        <img
          src={user.media.url}
          alt={user.username}
          className="img-circle mr-10"
          style={{ width: mediaSize, height: mediaSize }}
        />
      );
    }

    if (user && defaultAvatar !== null) {
      return (
        <img
          src={defaultAvatar}
          alt={user.username}
          className="img-circle mr-10"
          style={{ width: mediaSize, height: mediaSize }}
        />
      );
    }

    return <DefaultAvatar size={size} />;
  }

  render() {
    const { anchor, className, onBlur, onFocus, onMouseOut, onMouseOver, style, user } = this.props;
    const funcProps = {
      onBlur,
      onFocus,
      onMouseOver,
      onMouseOut,
    };

    if (user && user._links && user._links.profile && anchor) {
      return (
        <a {...funcProps} className={className} style={style} href={user._links.profile}>
          {this.renderAvatar()}
        </a>
      );
    }

    return (
      <span {...funcProps} className={className} style={style}>
        {this.renderAvatar()}
      </span>
    );
  }
}

const mapStateToProps: MapStateToProps<*, *, *> = (state: State) => ({
  defaultAvatar: state.default.images && state.default.images.avatar,
});

export default connect(mapStateToProps)(UserAvatar);
