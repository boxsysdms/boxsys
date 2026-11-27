import { createElement, type ComponentProps } from "react";

import { render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";

import Home from "./page";

vi.mock("next/image", () => ({
  default: (props: ComponentProps<"img">) => createElement("img", props),
}));

describe("Home page", () => {
  it("renders the starter heading", () => {
    render(createElement(Home));

    expect(
      screen.getByRole("heading", { name: /to get started, edit the page\.tsx file\./i }),
    ).toBeInTheDocument();
  });
});
