// @flow
import React from 'react';
import styled, { type StyledComponent } from 'styled-components';
import { FormattedMessage } from 'react-intl';
import { type FooterLink, type Legals } from './Footer';
import useShowMore from '../../utils/hooks/useShowMore';
import { useWindowWidth } from '~/utils/hooks/useWindowWidth';
import CookieManagerModal from '../StaticPage/CookieManagerModal';
import Menu from '../DesignSystem/Menu/Menu';
import MenuButton from '~ds/Menu/MenuButton';
import Button from '~ds/Button/Button';
import { ICON_NAME } from '~ds/Icon/Icon';
import colors from '~/styles/modules/colors';

type Props = {|
  links: Array<FooterLink>,
  legals: Legals,
  cookiesText: string,
  cookiesPath: string,
  privacyPath: string,
  legalPath: string,
  left?: boolean,
|};

export const LinkSeparator: StyledComponent<{}, {}, HTMLSpanElement> = styled.span`
  padding: 0 8px;
  @media (max-width: 767px) {
    display: none;
  }
`;

export const LinkList: StyledComponent<{ left?: boolean }, {}, HTMLUListElement> = styled.ul`
  width: 100%;
  text-align: left;
  list-style: none;
  margin: ${props => (props.left ? '0' : 'auto')};
  padding: 0;
  display: flex;
  justify-content: ${props => (!props.left ? 'center' : undefined)};
  flex-wrap: wrap;
  a {
    color: inherit;
  }
  @media (max-width: 767px) {
    text-align: center;
    flex-direction: column;
    li {
      padding-bottom: 5px;
    }
  }
`;

const SeeMoreFooterButton: StyledComponent<{}, {}, typeof MenuButton> = styled(Button)`
  background: transparent !important;
  border: none;
  color: inherit !important;
  font-size: inherit;
  display: flex;
  &:focus {
    box-shadow: none !important;
  }
  span {
    display: block;
    margin-top: -4px;
  }
  i {
    margin-top: -1px;
  }
`;

const getActivatedNumber = (legals: Legals, cookiesText: string) =>
  legals.cookies + legals.privacy + legals.legal + (cookiesText ? 1 : 0);

const renderSeeMore = (
  seeMoreRef: { current: null | HTMLElement },
  handleItemWidth: () => void,
  overflowIndex: number,
  links: Array<FooterLink>,
) => {
  return (
    <li key="see-more-footer" ref={seeMoreRef}>
      <LinkSeparator>|</LinkSeparator>
      <Menu>
        <Menu.Button>
          <SeeMoreFooterButton
            padding={0}
            id="footer-see-more-button"
            rightIcon={ICON_NAME.ARROW_DOWN_O}
            variant="primary"
            variantSize="small">
            <FormattedMessage id="global.navbar.see_more" />
          </SeeMoreFooterButton>
        </Menu.Button>
        <Menu.List>
          {links.map((link: FooterLink, index: number) => {
            return index >= overflowIndex ? (
              <Menu.ListItem
                style={{ color: colors.black }}
                as="a"
                href={link.url}
                key={link.name}
                ref={handleItemWidth}>
                {link.name}
              </Menu.ListItem>
            ) : null;
          })}
        </Menu.List>
      </Menu>
    </li>
  );
};

const FooterLinksRender = ({
  links,
  legals,
  cookiesPath,
  privacyPath,
  legalPath,
  cookiesText,
  left = false,
}: Props) => {
  const activeNumber = getActivatedNumber(legals, cookiesText);
  const { width } = useWindowWidth();
  const [containerRef, seeMoreRef, handleItemWidth, overflowIndex, shouldDisplaySeeMore] =
    useShowMore(width > 767, (links && links.length + activeNumber) || 0);
  return (
    <LinkList ref={containerRef}>
      {legals.cookies && (
        <li ref={handleItemWidth}>
          <a href={cookiesPath}>
            <FormattedMessage id="cookies" />
          </a>
        </li>
      )}
      <li ref={handleItemWidth}>
        <CookieManagerModal
          isLink
          separator={legals.cookies || legals.privacy || legals.legal ? '|' : ''}
        />
      </li>
      {legals.privacy && (
        <li ref={handleItemWidth} left={left}>
          {legals.cookies && <LinkSeparator>|</LinkSeparator>}
          <a href={privacyPath}>
            <FormattedMessage id="privacy-policy" />
          </a>
        </li>
      )}
      {legals.legal && (
        <li ref={handleItemWidth}>
          {(legals.privacy || legals.cookies) && <LinkSeparator>|</LinkSeparator>}
          <a href={legalPath}>
            <FormattedMessage id="legal-mentions" />
          </a>
        </li>
      )}
      {links.map((link: FooterLink, index: number) =>
        index < overflowIndex - activeNumber ? (
          <li key={link.name} ref={handleItemWidth}>
            {!index && (legals.legal || legals.privacy || legals.cookies || cookiesText) && (
              <LinkSeparator>|</LinkSeparator>
            )}
            <a href={link.url}>{link.name}</a>
            {index < overflowIndex - activeNumber - 1 && <LinkSeparator>|</LinkSeparator>}
          </li>
        ) : null,
      )}
      {shouldDisplaySeeMore &&
        renderSeeMore(seeMoreRef, handleItemWidth, overflowIndex - activeNumber, links)}
    </LinkList>
  );
};

export default FooterLinksRender;
