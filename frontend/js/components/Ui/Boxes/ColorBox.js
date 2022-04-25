// @flow
import * as React from 'react';
import styled, { type StyledComponent } from 'styled-components';
import tinycolor from 'tinycolor2';

type Props = {
  backgroundColor: string,
  children: React.Node,
  className: string,
  darkness?: number,
};

const BoxContainer: StyledComponent<Props, {}, HTMLDivElement> = styled.div`
  padding: 15px;
  background-color: ${props =>
    props.darkness && props.darkness > 0
      ? tinycolor(props.backgroundColor)
          .darken(props.darkness)
          .toString()
      : props.backgroundColor};
  border-radius: 4px;
`;

export const ColorBox = (props: Props) => {
  const { backgroundColor, children, className, darkness } = props;

  return (
    <BoxContainer darkness={darkness} className={className} backgroundColor={backgroundColor}>
      {children}
    </BoxContainer>
  );
};

ColorBox.defaultProps = {
  darkness: 0,
};

export default ColorBox;
