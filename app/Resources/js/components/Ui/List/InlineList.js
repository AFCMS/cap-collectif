// @flow
import * as React from 'react';
import styled from 'styled-components';

type Props = {
  margin: boolean,
  children: ?React.Node,
  className?: string,
};

const Container = styled.ul.attrs({
  className: 'inline-list',
})`
  padding: 0;
  margin: ${props => (props.margin === true ? '0 0 10px' : '0')};

  li {
    display: inline-block;

    &::after {
      content: '•';
      padding: 0 5px;
    }

    &:last-child::after {
      content: '';
      padding: 0;
    }
  }
`;

export const InlineList = (props: Props) => {
  const { children, margin, className } = props;

  return (
    <Container className={className} margin={margin}>
      {children}
    </Container>
  );
};

InlineList.defaultProps = {
  // eslint-disable-next-line react/default-props-match-prop-types
  margin: true,
};

export default InlineList;
