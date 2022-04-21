// @flow
import * as React from 'react';
import { FormattedMessage } from 'react-intl';
import { connect, type MapStateToProps } from 'react-redux';
import { Button } from 'react-bootstrap';
import LoginModal from './LoginModal';
import { baseUrl } from '../../../config';
import { showLoginModal } from '../../../redux/modules/user';
import type { Dispatch, State } from '../../../types';

type Props = {
  bsStyle: string,
  dispatch: Dispatch,
  className: ?string,
  style: ?Object,
  loginWithMonCompteParis: boolean,
};
export class LoginButton extends React.Component<Props> {
  static defaultProps = {
    bsStyle: 'default',
    className: '',
    style: {},
  };

  render() {
    const { dispatch, loginWithMonCompteParis, style, bsStyle, className } = this.props;
    return (
      <span style={style}>
        <Button
          bsStyle={bsStyle}
          onClick={() => {
            if (loginWithMonCompteParis) {
              const monCompteBaseUrl = 'https://moncompte.paris.fr/moncompte/';
              const monCompte_backUrlAuth = `${baseUrl}/login-paris?_destination=${
                window.location.href
              }`;
              const wH = 600;
              const wW = $(window).innerWidth() < 768 ? $(window).innerWidth() : 800;
              window.open(
                `${monCompteBaseUrl}jsp/site/Portal.jsp?page=myluteceusergu&view=createAccountModal&back_url=${monCompte_backUrlAuth}`,
                '_blank',
                `width=${wW},height=${wH},scrollbars=yes,status=yes,resizable=yes,toolbar=0,menubar=0,location=0,screenx=0,screeny=0`,
              );
            } else {
              dispatch(showLoginModal());
            }
          }}
          className={className}>
          <FormattedMessage id="global.login" />
        </Button>
        <LoginModal />
      </span>
    );
  }
}

const mapStateToProps: MapStateToProps<*, *, *> = (state: State) => ({
  loginWithMonCompteParis: state.default.features.login_paris,
});

const connector = connect(mapStateToProps);
export default connector(LoginButton);
